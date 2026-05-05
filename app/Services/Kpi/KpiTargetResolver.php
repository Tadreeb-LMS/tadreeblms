<?php

namespace App\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiTarget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class KpiTargetResolver
{
    public function resolveComparison(Kpi $kpi, float $actualValue, ?int $roleId = null, array $courseIds = []): array
    {
        $targetData = $this->resolveTarget($kpi, $roleId, $courseIds);
        $target = $targetData['target'];

        if ($target === null) {
            return [
                'target' => null,
                'target_scope' => 'none',
                'deviation_value' => null,
                'deviation_percentage' => null,
                'deviation_direction' => null,
            ];
        }

        $deviationValue = round($actualValue - $target, 2);

        if (abs($deviationValue) < 0.01) {
            $direction = 'on_target';
        } elseif ($deviationValue > 0) {
            $direction = 'over';
        } else {
            $direction = 'under';
        }

        $deviationPercentage = null;
        if ($target > 0) {
            $deviationPercentage = round((($actualValue - $target) / $target) * 100, 2);
        }

        return [
            'target' => round((float) $target, 2),
            'target_scope' => $targetData['scope'],
            'deviation_value' => $deviationValue,
            'deviation_percentage' => $deviationPercentage,
            'deviation_direction' => $direction,
        ];
    }

    protected function resolveTarget(Kpi $kpi, ?int $roleId = null, array $courseIds = []): array
    {
        if (!$this->tableExists()) {
            return ['target' => null, 'scope' => 'none'];
        }

        $courseIds = collect($courseIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($roleId !== null && !empty($courseIds)) {
            $roleCourseTargets = KpiTarget::query()
                ->where('kpi_id', $kpi->id)
                ->where('role_id', $roleId)
                ->whereIn('course_id', $courseIds)
                ->pluck('target_value');

            if ($roleCourseTargets->isNotEmpty()) {
                return [
                    'target' => $this->average($roleCourseTargets),
                    'scope' => 'role_course',
                ];
            }
        }

        if ($roleId !== null) {
            $roleTarget = KpiTarget::query()
                ->where('kpi_id', $kpi->id)
                ->where('role_id', $roleId)
                ->whereNull('course_id')
                ->value('target_value');

            if ($roleTarget !== null) {
                return ['target' => (float) $roleTarget, 'scope' => 'role'];
            }
        }

        if (!empty($courseIds)) {
            $courseTargets = KpiTarget::query()
                ->where('kpi_id', $kpi->id)
                ->whereNull('role_id')
                ->whereIn('course_id', $courseIds)
                ->pluck('target_value');

            if ($courseTargets->isNotEmpty()) {
                return [
                    'target' => $this->average($courseTargets),
                    'scope' => 'course',
                ];
            }
        }

        $globalTarget = KpiTarget::query()
            ->where('kpi_id', $kpi->id)
            ->whereNull('role_id')
            ->whereNull('course_id')
            ->value('target_value');

        if ($globalTarget !== null) {
            return ['target' => (float) $globalTarget, 'scope' => 'global'];
        }

        return ['target' => null, 'scope' => 'none'];
    }

    protected function average(Collection $values): ?float
    {
        if ($values->isEmpty()) {
            return null;
        }

        return (float) $values->avg();
    }

    protected function tableExists(): bool
    {
        return Schema::hasTable('kpi_targets');
    }
}
