<?php

namespace App\Jobs;

use App\Exports\KpiMetricsExport;
use App\Models\KpiExportNotification;
use App\Services\Kpi\KpiExportDatasetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class GenerateKpiReportExport implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * @var int
     */
    protected $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle(KpiExportDatasetService $datasetService): void
    {
        /** @var KpiExportNotification|null $export */
        $export = KpiExportNotification::query()->find($this->exportId);
        if (!$export) {
            return;
        }

        try {
            $filters = (array) ($export->filters ?? []);
            $format = strtolower((string) $export->format) === 'xlsx' ? 'xlsx' : 'csv';

            $totalUsers = $datasetService->countUsers($filters);
            $totalKpis = max(1, $datasetService->countKpis($filters));

            $export->update([
                'status' => 'processing',
                'progress' => 5,
                'processed_rows' => 0,
                'total_rows' => $totalUsers * $totalKpis,
                'error_message' => null,
            ]);

            $fileName = 'kpi_export_' . $export->id . '_' . time() . '.' . $format;
            $relativePath = 'reports/' . $fileName;

            $progressCallback = function (int $processedUsers, int $totalUsersInProgress) use ($export, $totalKpis) {
                $processedRows = $processedUsers * max(1, $totalKpis);
                $progress = (int) min(95, max(10, round(($processedUsers / max(1, $totalUsersInProgress)) * 90)));

                KpiExportNotification::query()
                    ->where('id', $export->id)
                    ->update([
                        'processed_rows' => $processedRows,
                        'progress' => $progress,
                    ]);
            };

            $writerType = $format === 'xlsx' ? ExcelFormat::XLSX : ExcelFormat::CSV;
            $raw = Excel::raw(new KpiMetricsExport($filters, $datasetService, $progressCallback), $writerType);

            Storage::disk('public')->put($relativePath, $raw);

            $downloadUrl = asset('storage/' . $relativePath);

            $export->update([
                'status' => 'completed',
                'progress' => 100,
                'file_path' => $relativePath,
                'download_link' => $downloadUrl,
                'processed_rows' => $export->total_rows,
            ]);
        } catch (\Throwable $e) {
            $export->update([
                'status' => 'failed',
                'progress' => 100,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
