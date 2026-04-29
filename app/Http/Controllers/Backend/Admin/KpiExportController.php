<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateKpiReportExport;
use App\Models\KpiExportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KpiExportController extends Controller
{
    public function store(Request $request)
    {
        if (!Gate::allows('kpi_access')) {
            return abort(401);
        }

        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx',
            'role' => 'nullable|string|exists:roles,name',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'kpi_ids' => 'nullable|array',
            'kpi_ids.*' => 'integer|exists:kpis,id',
        ]);

        $filters = [
            'role' => $validated['role'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'kpi_ids' => array_values(array_unique(array_map('intval', $validated['kpi_ids'] ?? []))),
        ];

        KpiExportNotification::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'failed',
                'progress' => 100,
                'error_message' => 'Canceled by newer export request.',
            ]);

        $export = KpiExportNotification::query()->create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'progress' => 0,
            'format' => $validated['format'],
            'filters' => $filters,
        ]);

        GenerateKpiReportExport::dispatch((int) $export->id);

        return response()->json([
            'status' => true,
            'export_id' => (int) $export->id,
            'message' => 'KPI export started. Please wait while the file is generated.',
        ]);
    }

    public function status($export)
    {
        if (!Gate::allows('kpi_access')) {
            return abort(401);
        }

        $exportRow = KpiExportNotification::query()
            ->where('id', (int) $export)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'export_id' => (int) $exportRow->id,
            'state' => (string) $exportRow->status,
            'progress' => (int) $exportRow->progress,
            'download_link' => $exportRow->download_link,
            'error_message' => $exportRow->error_message,
            'processed_rows' => (int) $exportRow->processed_rows,
            'total_rows' => (int) $exportRow->total_rows,
        ]);
    }
}
