<?php

namespace App\Services;

use App\Services\Kpi\KpiMetricDataProvider;
use App\Services\Kpi\KpiProcessingEngine;

class KpiCalculationService
{
    protected $engine;

    protected $metricDataProvider;

    protected $typeValueCache = [];

    public function __construct(KpiProcessingEngine $engine, KpiMetricDataProvider $metricDataProvider)
    {
        $this->engine = $engine;
        $this->metricDataProvider = $metricDataProvider;
    }

    public function getSupportedTypeKeys()
    {
        return array_keys(config('kpi.types', []));
    }

    public function calculateForKpi($kpi, $totalActiveWeight)
    {
        $kpiCourseIds = $this->resolveKpiCourseIds($kpi);
        $value = $this->calculateTypeValueForCourses($kpi->type, $kpiCourseIds);

        return $this->engine->calculate([
            'type' => $kpi->type,
            'weight' => $kpi->weight,
            'is_active' => (bool) $kpi->is_active,
        ], [
            'value' => $value,
        ], (float) $totalActiveWeight);
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
