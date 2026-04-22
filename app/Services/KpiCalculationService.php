<?php

namespace App\Services;

use App\Services\Kpi\KpiMetricDataProvider;
use App\Services\Kpi\KpiProcessingEngine;
use App\Services\Kpi\KpiRoleConfigResolver;
use App\Services\Kpi\KpiTypeCatalog;

class KpiCalculationService
{
    protected $engine;

    protected $metricDataProvider;

    protected $roleConfigResolver;

    protected $kpiTypeCatalog;

    protected $typeValueCache = [];

    public function __construct(
        KpiProcessingEngine $engine,
        KpiMetricDataProvider $metricDataProvider,
        KpiRoleConfigResolver $roleConfigResolver,
        KpiTypeCatalog $kpiTypeCatalog
    ) {
        $this->engine = $engine;
        $this->metricDataProvider = $metricDataProvider;
        $this->roleConfigResolver = $roleConfigResolver;
        $this->kpiTypeCatalog = $kpiTypeCatalog;
    }

    public function getSupportedTypeKeys(): array
    {
        return $this->kpiTypeCatalog->getSupportedKeys();
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