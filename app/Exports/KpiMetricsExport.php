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
            'Role Filter',
            'User ID',
            'User Name',
            'User Email',
            'KPI ID',
            'KPI Code',
            'KPI Name',
            'KPI Type',
            'Metric Value',
            'KPI Weight',
            'Weighted Score',
            'Date From',
            'Date To',
        ];
    }
}
