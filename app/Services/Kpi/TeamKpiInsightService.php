<?php

namespace App\Services\Kpi;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamKpiInsightService
{
    /**
     * @param Collection $kpis
     * @param array $userIds
     * @param array $memberDirectory
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return array
     */
    public function buildInsights(Collection $kpis, array $userIds, array $memberDirectory, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $userIds = collect($userIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $memberScores = [];
        foreach ($userIds as $userId) {
            $memberScores[$userId] = [
                'weighted_total' => 0.0,
                'weight_total' => 0.0,
            ];
        }

        $summaries = [];

        foreach ($kpis as $kpi) {
            $courseIds = method_exists($kpi, 'resolveScopedCourseIds') ? $kpi->resolveScopedCourseIds() : [];
            $valuesByUser = $this->getValuesByUserForType((string) $kpi->type, $userIds, $courseIds, $dateFrom, $dateTo);

            $ranked = collect($valuesByUser)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->map(function ($value, $userId) {
                    return [
                        'user_id' => (int) $userId,
                        'value' => round((float) $value, 2),
                    ];
                })
                ->sortByDesc('value')
                ->values();

            $evaluatedCount = $ranked->count();
            $avg = $evaluatedCount > 0 ? round((float) $ranked->avg('value'), 2) : null;
            $top = $evaluatedCount > 0 ? $ranked->first() : null;
            $bottom = $evaluatedCount > 0 ? $ranked->last() : null;

            $kpiWeight = (float) ($kpi->weight ?? 0);
            foreach ($ranked as $entry) {
                if (!isset($memberScores[$entry['user_id']])) {
                    continue;
                }

                $memberScores[$entry['user_id']]['weighted_total'] += ((float) $entry['value']) * $kpiWeight;
                $memberScores[$entry['user_id']]['weight_total'] += $kpiWeight;
            }

            $summaries[] = [
                'id' => (int) $kpi->id,
                'name' => (string) $kpi->name,
                'code' => (string) $kpi->code,
                'type' => (string) $kpi->type,
                'type_label' => (string) ($kpi->type_label ?? ucfirst((string) $kpi->type)),
                'weight' => round((float) $kpiWeight, 2),
                'team_average' => $avg,
                'members_evaluated' => $evaluatedCount,
                'top_performer' => $this->resolvePerformerPayload($top, $memberDirectory),
                'bottom_performer' => $this->resolvePerformerPayload($bottom, $memberDirectory),
                'spread' => ($top && $bottom) ? round((float) $top['value'] - (float) $bottom['value'], 2) : null,
            ];
        }

        $memberComparisons = collect($memberScores)
            ->map(function ($score, $userId) use ($memberDirectory) {
                $overall = $score['weight_total'] > 0
                    ? round($score['weighted_total'] / $score['weight_total'], 2)
                    : null;

                return [
                    'user_id' => (int) $userId,
                    'name' => $memberDirectory[$userId] ?? ('User #' . $userId),
                    'overall_score' => $overall,
                ];
            })
            ->filter(function ($entry) {
                return $entry['overall_score'] !== null;
            })
            ->sortByDesc('overall_score')
            ->values();

        return [
            'kpi_summaries' => $summaries,
            'top_performers' => $memberComparisons->take(5)->values()->all(),
            'bottom_performers' => $memberComparisons->sortBy('overall_score')->take(5)->values()->all(),
            'team_score_average' => $memberComparisons->isNotEmpty() ? round((float) $memberComparisons->avg('overall_score'), 2) : null,
            'team_member_count' => count($userIds),
            'evaluated_member_count' => $memberComparisons->count(),
        ];
    }

    /**
     * @param string $type
     * @param array $userIds
     * @param array $courseIds
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return array<int, float|null>
     */
    protected function getValuesByUserForType(string $type, array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $result = [];
        foreach ($userIds as $userId) {
            $result[(int) $userId] = null;
        }

        if (empty($userIds)) {
            return $result;
        }

        $metricRows = [];
        switch ($type) {
            case 'completion':
                $metricRows = $this->completionByUser($userIds, $courseIds, $dateFrom, $dateTo);
                break;
            case 'score':
                $metricRows = $this->scoreByUser($userIds, $courseIds, $dateFrom, $dateTo);
                break;
            case 'activity':
                $metricRows = $this->activityByUser($userIds, $dateFrom, $dateTo);
                break;
            case 'time':
                $metricRows = $this->timeByUser($userIds, $dateFrom, $dateTo);
                break;
            default:
                return $result;
        }

        foreach ($metricRows as $userId => $value) {
            $id = (int) $userId;
            if (!array_key_exists($id, $result)) {
                continue;
            }
            $result[$id] = round((float) $value, 2);
        }

        return $result;
    }

    protected function completionByUser(array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('subscribe_courses') || !Schema::hasColumn('subscribe_courses', 'user_id')) {
            return [];
        }

        $query = DB::table('subscribe_courses')
            ->select('user_id', DB::raw('AVG(CASE WHEN is_completed = 1 THEN 100 ELSE 0 END) as metric'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (Schema::hasColumn('subscribe_courses', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (!empty($courseIds) && Schema::hasColumn('subscribe_courses', 'course_id')) {
            $query->whereIn('course_id', $courseIds);
        }

        if (Schema::hasColumn('subscribe_courses', 'completed_at')) {
            $this->applyDateRange($query, 'completed_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('subscribe_courses', 'updated_at')) {
            $this->applyDateRange($query, 'updated_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('subscribe_courses', 'created_at')) {
            $this->applyDateRange($query, 'created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'user_id')->toArray();
    }

    protected function scoreByUser(array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('tests_results') || !Schema::hasColumn('tests_results', 'user_id')) {
            return [];
        }

        $query = DB::table('tests_results')
            ->select('tests_results.user_id', DB::raw('AVG(tests_results.test_result) as metric'))
            ->whereIn('tests_results.user_id', $userIds)
            ->groupBy('tests_results.user_id');

        if (Schema::hasTable('tests') && Schema::hasColumn('tests_results', 'test_id') && Schema::hasColumn('tests', 'id')) {
            $query->join('tests', 'tests.id', '=', 'tests_results.test_id');
            if (!empty($courseIds) && Schema::hasColumn('tests', 'course_id')) {
                $query->whereIn('tests.course_id', $courseIds);
            }
            if (Schema::hasColumn('tests', 'deleted_at')) {
                $query->whereNull('tests.deleted_at');
            }
        }

        if (Schema::hasColumn('tests_results', 'created_at')) {
            $this->applyDateRange($query, 'tests_results.created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'tests_results.user_id')->toArray();
    }

    protected function activityByUser(array $userIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (Schema::hasTable('lms_kpi_events') && Schema::hasColumn('lms_kpi_events', 'user_id')) {
            $query = DB::table('lms_kpi_events')
                ->select('user_id', DB::raw('LEAST(100, COUNT(*) * 10) as metric'))
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id');

            if (Schema::hasColumn('lms_kpi_events', 'occurred_at')) {
                $this->applyDateRange($query, 'occurred_at', $dateFrom, $dateTo);
            }

            return $query->pluck('metric', 'user_id')->toArray();
        }

        return [];
    }

    protected function timeByUser(array $userIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('video_progresses') || !Schema::hasColumn('video_progresses', 'user_id')) {
            return [];
        }

        $query = DB::table('video_progresses')
            ->select('user_id', DB::raw('LEAST(100, SUM(progress) / 60) as metric'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (Schema::hasColumn('video_progresses', 'updated_at')) {
            $this->applyDateRange($query, 'updated_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('video_progresses', 'created_at')) {
            $this->applyDateRange($query, 'created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'user_id')->toArray();
    }

    /**
     * @param mixed $performer
     * @param array $memberDirectory
     * @return array|null
     */
    protected function resolvePerformerPayload($performer, array $memberDirectory): ?array
    {
        if (!$performer) {
            return null;
        }

        $userId = (int) ($performer['user_id'] ?? 0);

        return [
            'user_id' => $userId,
            'name' => $memberDirectory[$userId] ?? ('User #' . $userId),
            'value' => round((float) ($performer['value'] ?? 0), 2),
        ];
    }

    /**
     * @param mixed $query
     * @param string $column
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return void
     */
    protected function applyDateRange($query, string $column, ?Carbon $dateFrom, ?Carbon $dateTo): void
    {
        if ($dateFrom) {
            $query->where($column, '>=', $dateFrom->copy()->startOfDay());
        }

        if ($dateTo) {
            $query->where($column, '<=', $dateTo->copy()->endOfDay());
        }
    }
}
