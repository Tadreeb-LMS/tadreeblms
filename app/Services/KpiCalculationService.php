<?php

namespace App\Services;

use App\Services\Kpi\KpiMetricDataProvider;
use App\Services\Kpi\KpiProcessingEngine;
use App\Services\Kpi\KpiRoleConfigResolver;

class KpiCalculationService
{
    protected $engine;

    protected $metricDataProvider;

    protected $roleConfigResolver;

    protected $typeValueCache = [];

    public function __construct(
        KpiProcessingEngine $engine,
        KpiMetricDataProvider $metricDataProvider,
        KpiRoleConfigResolver $roleConfigResolver
    ) {
        $this->engine = $engine;
        $this->metricDataProvider = $metricDataProvider;
        $this->roleConfigResolver = $roleConfigResolver;
    }

    public function getSupportedTypeKeys()
    {
        return array_keys(config('kpi.types', []));
    }

    public function calculateForKpi($kpi, $totalActiveWeight, ?int $roleId = null)
    {
        $kpiConfig = $roleId !== null
            ? $this->roleConfigResolver->resolve($kpi, $roleId)
            : ['type' => $kpi->type, 'weight' => (float) $kpi->weight, 'is_active' => (bool) $kpi->is_active];

        $kpiCourseIds = $this->resolveKpiCourseIds($kpi);
        $value = $this->calculateTypeValueForCourses($kpiConfig['type'], $kpiCourseIds);

        return $this->engine->calculate($kpiConfig, ['value' => $value], (float) $totalActiveWeight);
    }

    public function calculateTypeValue($type)
    {
        return $this->calculateTypeValueForCourses($type, []);
    }

    protected function calculateTypeValueForCourses($type, array $courseIds)
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
