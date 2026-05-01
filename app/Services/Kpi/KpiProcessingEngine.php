<?php

namespace App\Services\Kpi;

class KpiProcessingEngine
{
    /**
     * Pure KPI computation that depends only on supplied inputs.
     *
     * @param array $kpiConfig
     * @param array $metricPayload
     * @param float $totalActiveWeight
     * @return array
     */
    public function calculate(array $kpiConfig, array $metricPayload, float $totalActiveWeight)
    {
        $isActive = (bool) ($kpiConfig['is_active'] ?? false);
        if (!$isActive) {
            return [
                'excluded' => true,
                'value' => null,
                'weighted_score' => null,
            ];
        }

        $value = $this->resolveMetricValue($kpiConfig, $metricPayload);
        $weight = max(0.0, (float) ($kpiConfig['weight'] ?? 0));
        $normalizedTotalWeight = max(0.0, $totalActiveWeight);

        $weightedScore = 0.0;
        if ($normalizedTotalWeight > 0) {
            $weightedScore = ($value * $weight) / $normalizedTotalWeight;
        }

        return [
            'excluded' => false,
            'value' => round($value, 2),
            'weighted_score' => round($weightedScore, 2),
        ];
    }

    /**
     * @param array $kpiConfig
     * @param array $metricPayload
     * @return float
     */
    protected function resolveMetricValue(array $kpiConfig, array $metricPayload)
    {
        if (array_key_exists('value', $metricPayload)) {
            return $this->normalizePercent($metricPayload['value']);
        }

        $type = (string) ($kpiConfig['type'] ?? '');

        if (isset($metricPayload['by_type']) && is_array($metricPayload['by_type']) && array_key_exists($type, $metricPayload['by_type'])) {
            return $this->normalizePercent($metricPayload['by_type'][$type]);
        }

        if ($type !== '' && array_key_exists($type, $metricPayload)) {
            return $this->normalizePercent($metricPayload[$type]);
        }

        return 0.0;
    }

    /**
     * @param mixed $value
     * @return float
     */
    protected function normalizePercent($value)
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round(min(100.0, max(0.0, (float) $value)), 2);
    }
}
