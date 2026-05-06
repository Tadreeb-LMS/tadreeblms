<?php

namespace App\Exports;

use App\Services\Kpi\KpiExportDatasetService;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KpiMetricsExport implements FromGenerator, WithHeadings
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var KpiExportDatasetService
     */
    protected $datasetService;

    /**
     * @var callable|null
     */
    protected $progressCallback;

    public function __construct(array $filters, KpiExportDatasetService $datasetService, callable $progressCallback = null)
    {
        $this->filters = $filters;
        $this->datasetService = $datasetService;
        $this->progressCallback = $progressCallback;
    }

    /**
     * @return Generator
     */
    public function generator(): Generator
    {
        yield from $this->datasetService->generateRows($this->filters, $this->progressCallback);
    }

    public function headings(): array
    {
        return [
            __('kpi.labels.role_filter'),
            __('kpi.labels.user_id'),
            __('kpi.labels.user_name'),
            __('kpi.labels.user_email'),
            __('kpi.labels.kpi_id'),
            __('kpi.labels.kpi_code'),
            __('kpi.labels.kpi_name'),
            __('kpi.labels.kpi_type'),
            __('kpi.labels.metric_value'),
            __('kpi.labels.weight'),
            __('kpi.labels.weighted_score'),
            __('kpi.labels.date_from'),
            __('kpi.labels.date_to'),
        ];
    }
}
