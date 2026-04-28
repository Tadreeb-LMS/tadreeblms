<?php

namespace App\Services\Kpi;

use App\Models\KpiType;
use Illuminate\Support\Facades\Schema;

class KpiTypeCatalog
{
    /**
     * @var KpiMetricDataProvider
     */
    protected $metricDataProvider;

    /**
     * @var array<string, array>|null
     */
    protected $supportedOptionsCache;

    public function __construct(KpiMetricDataProvider $metricDataProvider)
    {
        $this->metricDataProvider = $metricDataProvider;
    }

    /**
     * @return string[]
     */
    public function getSupportedKeys(): array
    {
        return array_keys($this->getSupportedOptions());
    }

    /**
     * @return array<string, array>
     */
    public function getSupportedOptions(): array
    {
        if (is_array($this->supportedOptionsCache)) {
            return $this->supportedOptionsCache;
        }

        if (!Schema::hasTable('kpi_types')) {
            $this->supportedOptionsCache = [];

            return $this->supportedOptionsCache;
        }

        $rows = KpiType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['key', 'label', 'description']);

        $supported = [];
        foreach ($rows as $row) {
            $key = trim((string) $row->key);
            if ($key === '' || !$this->metricDataProvider->supportsType($key)) {
                continue;
            }

            $supported[$key] = [
                'label' => (string) $row->label,
                'description' => (string) ($row->description ?? ''),
            ];
        }

        $this->supportedOptionsCache = $supported;

        return $this->supportedOptionsCache;
    }

    public function getLabelForType(string $type): string
    {
        $options = $this->getSupportedOptions();
        if (isset($options[$type]['label'])) {
            return (string) $options[$type]['label'];
        }

        return ucfirst(str_replace('_', ' ', $type));
    }
}
