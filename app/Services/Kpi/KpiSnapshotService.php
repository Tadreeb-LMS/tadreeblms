<?php

namespace App\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiSnapshot;
use App\Services\KpiCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KpiSnapshotService
{
    protected $calculationService;

    public function __construct(KpiCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * @param Collection $kpis
     * @param float $totalActiveWeight
     * @return Collection
     */
    public function attachCalculations(Collection $kpis, $totalActiveWeight)
    {
        if ($kpis->isEmpty()) {
            return $kpis;
        }

        $totalActiveWeight = (float) $totalActiveWeight;

        if (!$this->canUseSnapshotStorage()) {
            return $this->attachLiveCalculations($kpis, $totalActiveWeight);
        }

        $kpis->loadMissing('currentSnapshot', 'courses:id', 'categories:id');

        $globalContext = $this->buildGlobalContext($totalActiveWeight);
        $globalContext['course_scope_signature'] = $this->buildCourseScopeSignatures($kpis);

        return $kpis->map(function ($kpi) use ($globalContext, $totalActiveWeight) {
            $snapshot = $this->resolveSnapshotForKpi($kpi, $globalContext, $totalActiveWeight);

            $kpi->calculation = [
                'excluded' => (bool) $snapshot->excluded,
                'value' => $snapshot->value === null ? null : (float) $snapshot->value,
                'weighted_score' => $snapshot->weighted_score === null ? null : (float) $snapshot->weighted_score,
            ];

            return $kpi;
        });
    }

    /**
     * @param Collection $kpis
     * @param float $totalActiveWeight
     * @return Collection
     */
    protected function attachLiveCalculations(Collection $kpis, $totalActiveWeight)
    {
        return $kpis->map(function ($kpi) use ($totalActiveWeight) {
            $kpi->calculation = $this->calculationService->calculateForKpi($kpi, $totalActiveWeight);

            return $kpi;
        });
    }

    /**
     * @param Kpi $kpi
     * @param array $globalContext
     * @param float $totalActiveWeight
     * @return KpiSnapshot
     */
    protected function resolveSnapshotForKpi(Kpi $kpi, array $globalContext, $totalActiveWeight)
    {
        $expectedSignature = $this->buildInputSignature($kpi, $globalContext);

        $currentSnapshot = $kpi->currentSnapshot;
        if ($this->isSnapshotReusable($currentSnapshot, $expectedSignature)) {
            return $currentSnapshot;
        }

        $calculation = $this->calculationService->calculateForKpi($kpi, $totalActiveWeight);

        $snapshot = $this->storeSnapshot($kpi, $calculation, $expectedSignature, $globalContext, $currentSnapshot, $totalActiveWeight);
        $kpi->setRelation('currentSnapshot', $snapshot);

        return $snapshot;
    }

    /**
     * @param KpiSnapshot|null $snapshot
     * @param string $expectedSignature
     * @return bool
     */
    protected function isSnapshotReusable($snapshot, $expectedSignature)
    {
        if (!$snapshot) {
            return false;
        }

        if ((string) $snapshot->input_signature !== (string) $expectedSignature) {
            return false;
        }

        if ((int) $snapshot->calculation_version !== (int) config('kpi.snapshots.version', 1)) {
            return false;
        }

        $maxAgeMinutes = max(1, (int) config('kpi.snapshots.max_age_minutes', 5));
        $validUntil = optional($snapshot->calculated_at)->copy()->addMinutes($maxAgeMinutes);

        return $validUntil instanceof Carbon && $validUntil->isFuture();
    }

    /**
     * @param Kpi $kpi
     * @param array $calculation
     * @param string $inputSignature
     * @param array $globalContext
     * @param KpiSnapshot|null $currentSnapshot
     * @param float $totalActiveWeight
     * @return KpiSnapshot
     */
    protected function storeSnapshot(Kpi $kpi, array $calculation, $inputSignature, array $globalContext, $currentSnapshot, $totalActiveWeight)
    {
        return DB::transaction(function () use ($kpi, $calculation, $inputSignature, $globalContext, $currentSnapshot, $totalActiveWeight) {
            KpiSnapshot::query()
                ->where('kpi_id', $kpi->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'updated_at' => now(),
                ]);

            return KpiSnapshot::query()->create([
                'kpi_id' => $kpi->id,
                'previous_snapshot_id' => optional($currentSnapshot)->id,
                'calculation_version' => (int) config('kpi.snapshots.version', 1),
                'input_signature' => $inputSignature,
                'excluded' => (bool) ($calculation['excluded'] ?? false),
                'value' => array_key_exists('value', $calculation) ? $calculation['value'] : null,
                'weighted_score' => array_key_exists('weighted_score', $calculation) ? $calculation['weighted_score'] : null,
                'total_active_weight' => round((float) $totalActiveWeight, 2),
                'is_current' => true,
                'calculated_at' => now(),
                'meta' => [
                    'event_max_id' => $globalContext['event_max_id'],
                    'course_max_updated_at' => $globalContext['course_max_updated_at'],
                    'course_scope_signature' => $globalContext['course_scope_signature'][$kpi->id] ?? null,
                ],
            ]);
        });
    }

    /**
     * @param Kpi $kpi
     * @param array $globalContext
     * @return string
     */
    protected function buildInputSignature(Kpi $kpi, array $globalContext)
    {
        $courseScopeSignature = $globalContext['course_scope_signature'][$kpi->id] ?? '';

        return sha1(implode('|', [
            (int) config('kpi.snapshots.version', 1),
            (int) $kpi->id,
            (string) $kpi->type,
            round((float) $kpi->weight, 4),
            (int) (bool) $kpi->is_active,
            $this->normalizeDateForSignature($kpi->updated_at),
            round((float) $globalContext['total_active_weight'], 4),
            (string) $globalContext['event_max_id'],
            (string) $globalContext['course_max_updated_at'],
            (string) $courseScopeSignature,
        ]));
    }

    /**
     * @param float $totalActiveWeight
     * @return array
     */
    protected function buildGlobalContext($totalActiveWeight)
    {
        $context = [
            'total_active_weight' => (float) $totalActiveWeight,
            'event_max_id' => null,
            'course_max_updated_at' => null,
        ];

        if (Schema::hasTable('lms_kpi_events') && Schema::hasColumn('lms_kpi_events', 'id')) {
            $context['event_max_id'] = (string) DB::table('lms_kpi_events')->max('id');
        }

        if (
            Schema::hasTable('courses')
            && Schema::hasColumn('courses', 'updated_at')
            && Schema::hasColumn('courses', 'id')
        ) {
            $courseQuery = DB::table('courses');
            if (Schema::hasColumn('courses', 'deleted_at')) {
                $courseQuery->whereNull('deleted_at');
            }
            if (Schema::hasColumn('courses', 'include_in_kpi')) {
                $courseQuery->where('include_in_kpi', true);
            }

            $context['course_max_updated_at'] = $this->normalizeDateForSignature($courseQuery->max('updated_at'));
        }

        return $context;
    }

    /**
     * @param Collection $kpis
     * @return array
     */
    protected function buildCourseScopeSignatures(Collection $kpis)
    {
        $signatures = [];

        foreach ($kpis as $kpi) {
            $courseIds = method_exists($kpi, 'resolveScopedCourseIds')
                ? collect($kpi->resolveScopedCourseIds())->sort()->values()->toArray()
                : $kpi->courses
                    ->pluck('id')
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

            $signatures[$kpi->id] = sha1(implode(',', $courseIds));
        }

        return $signatures;
    }

    /**
     * @param mixed $date
     * @return string
     */
    protected function normalizeDateForSignature($date)
    {
        if (!$date) {
            return '';
        }

        try {
            return Carbon::parse($date)->toAtomString();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    protected function canUseSnapshotStorage()
    {
        return Schema::hasTable('kpi_snapshots');
    }
}
