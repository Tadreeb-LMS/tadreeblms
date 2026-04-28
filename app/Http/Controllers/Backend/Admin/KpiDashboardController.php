<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\Kpi\KpiDashboardConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class KpiDashboardController extends Controller
{
    protected $configService;

    public function __construct(KpiDashboardConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function edit()
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('administrator') || !Gate::allows('kpi_edit')) {
            return abort(403);
        }

        $configuration = $this->configService->getEditableConfiguration();

        return view('backend.kpis.dashboard_settings', $configuration);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('administrator') || !Gate::allows('kpi_edit')) {
            return abort(403);
        }

        $validated = $request->validate([
            'visible_kpis' => 'nullable|array|max:8',
            'visible_kpis.*' => 'integer|distinct|exists:kpis,id',
            'presentation' => 'nullable|array',
            'presentation.*' => 'nullable|string|in:compact,detail',
            'display_order' => 'nullable|array',
            'display_order.*' => 'nullable|integer|min:1|max:99',
        ]);

        $this->configService->updateConfiguration(
            $validated['visible_kpis'] ?? [],
            $validated['presentation'] ?? [],
            $validated['display_order'] ?? [],
            $user->id
        );

        return redirect()
            ->route('admin.kpis.dashboard-settings.edit')
            ->withFlashSuccess('KPI dashboard settings updated successfully.');
    }
}
