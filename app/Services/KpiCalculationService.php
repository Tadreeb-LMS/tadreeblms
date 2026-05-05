<?php

namespace App\Services;

use App\Services\Kpi\KpiMetricDataProvider;
use App\Services\Kpi\KpiProcessingEngine;
use App\Services\Kpi\KpiRoleConfigResolver;
use App\Services\Kpi\KpiTargetResolver;
use App\Services\Kpi\KpiTypeCatalog;

class KpiCalculationService
{
    protected $engine;

    protected $metricDataProvider;

    protected $roleConfigResolver;

    protected $kpiTypeCatalog;

    protected $targetResolver;

    protected $typeValueCache = [];

    public function __construct(
        KpiProcessingEngine $engine,
        KpiMetricDataProvider $metricDataProvider,
        KpiRoleConfigResolver $roleConfigResolver,
        KpiTargetResolver $targetResolver,
        KpiTypeCatalog $kpiTypeCatalog
    ) {
        $this->engine = $engine;
        $this->metricDataProvider = $metricDataProvider;
        $this->roleConfigResolver = $roleConfigResolver;
        $this->targetResolver = $targetResolver;
        $this->kpiTypeCatalog = $kpiTypeCatalog;
    }

    public function getSupportedTypeKeys(): array
    {
        return $this->kpiTypeCatalog->getSupportedKeys();
    }

    public function calculateForKpi($kpi, $totalActiveWeight, ?int $roleId = null)
    {
        $baseCalculation = $this->calculateBaseForKpi($kpi, $totalActiveWeight, $roleId);

        return array_merge(
            $baseCalculation,
            $this->calculateTargetComparison(
                $kpi,
                $baseCalculation['value'],
                $roleId,
                $this->resolveKpiCourseIds($kpi)
            )
        );
    }

    public function calculateBaseForKpi($kpi, $totalActiveWeight, ?int $roleId = null)
    {
        $kpiConfig = $roleId !== null
            ? $this->roleConfigResolver->resolve($kpi, $roleId)
            : ['type' => $kpi->type, 'weight' => (float) $kpi->weight, 'is_active' => (bool) $kpi->is_active];

        $kpiCourseIds = $this->resolveKpiCourseIds($kpi);
        $value = $this->calculateTypeValueForCourses($kpiConfig['type'], $kpiCourseIds);

        $calculation = $this->engine->calculate($kpiConfig, ['value' => $value], (float) $totalActiveWeight);

        return $calculation;
    }

    public function calculateTargetComparison($kpi, $actualValue, ?int $roleId = null, array $courseIds = []): array
    {
        if ($actualValue === null) {
            return [
                'target' => null,
                'target_scope' => 'none',
                'deviation_value' => null,
                'deviation_percentage' => null,
                'deviation_direction' => null,
            ];
        }

        return $this->targetResolver->resolveComparison($kpi, (float) $actualValue, $roleId, $courseIds);
    }

    public function calculateTypeValue($type): float
    {
        return $this->calculateTypeValueForCourses($type, []);
    }

    protected function calculateTypeValueForCourses($type, array $courseIds): float
    {
        $cacheKey = sprintf('%s|%s', (string) $type, implode(',', $courseIds));
        if (array_key_exists($cacheKey, $this->typeValueCache)) {
            return $this->typeValueCache[$cacheKey];
        }

        $value = $this->metricDataProvider->getMetricValueForType((string) $type, $courseIds);

        $this->typeValueCache[$cacheKey] = $value;

        return $value;
    }

    protected function resolveKpiCourseIds($kpi)
    {
        if (method_exists($kpi, 'resolveScopedCourseIds')) {
            return $kpi->resolveScopedCourseIds();
        }

        if (method_exists($kpi, 'relationLoaded') && $kpi->relationLoaded('courses')) {
            return $kpi->courses->pluck('id')->map(function ($id) {
                return (int) $id;
            })->filter()->unique()->values()->toArray();
        }

        if (method_exists($kpi, 'courses')) {
            return $kpi->courses()->pluck('courses.id')->map(function ($id) {
                return (int) $id;
            })->filter()->unique()->values()->toArray();
        }

        return [];
    }
}