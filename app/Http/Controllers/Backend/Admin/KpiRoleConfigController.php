<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\Role;
use App\Models\Kpi;
use App\Models\KpiRoleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KpiRoleConfigController extends Controller
{
    /**
     * Show the role configuration matrix for all active KPIs.
     * GET /admin/kpi-role-configs
     */
    public function index()
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpis  = Kpi::query()->where('is_active', true)->orderBy('name')->get();
        $roles = Role::query()->orderBy('name')->get();

        // Load all existing overrides keyed by [role_id][kpi_id]
        $overrides = KpiRoleConfig::query()->get()->groupBy('role_id')->map(function ($rows) {
            return $rows->keyBy('kpi_id');
        });

        return view('backend.kpis.role_configs.index', compact('kpis', 'roles', 'overrides'));
    }

    /**
     * Upsert a single role+kpi override.
     * POST /admin/kpi-role-configs
     */
    public function store(Request $request)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $validated = $request->validate([
            'role_id'            => ['required', 'integer', Rule::exists('roles', 'id')],
            'kpi_id'             => ['required', 'integer', Rule::exists('kpis', 'id')],
            'weight_override'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active_override' => ['nullable', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        $isActiveOverride = isset($validated['is_active_override'])
            ? (bool) $validated['is_active_override']
            : null;

        $attrs = [
            'weight_override'    => isset($validated['weight_override']) ? (float) $validated['weight_override'] : null,
            'is_active_override' => $isActiveOverride,
        ];

        KpiRoleConfig::query()->updateOrCreate(
            ['role_id' => $validated['role_id'], 'kpi_id' => $validated['kpi_id']],
            $attrs
        );

        return redirect()->back()->with('flash_success', 'Role KPI configuration saved.');
    }

    /**
     * Remove a role override, returning the KPI to its global default for that role.
     * DELETE /admin/kpi-role-configs/{kpiRoleConfig}
     */
    public function destroy(KpiRoleConfig $kpiRoleConfig)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpiRoleConfig->delete();

        return redirect()->back()->with('flash_success', 'Role override removed. Global default will apply.');
    }
}
