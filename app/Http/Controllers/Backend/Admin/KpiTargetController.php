<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\Role;
use App\Models\Course;
use App\Models\Kpi;
use App\Models\KpiTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KpiTargetController extends Controller
{
    public function index()
    {
        if (!$this->canAccessTargets()) {
            return abort(401);
        }

        $kpis = Kpi::query()->orderBy('name')->get(['id', 'name', 'code']);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title', 'course_code']);
        $targets = KpiTarget::query()
            ->with(['kpi:id,name,code', 'role:id,name', 'course:id,title,course_code'])
            ->orderByDesc('id')
            ->get();

        $canManage = $this->canEditTargets();

        return view('backend.kpis.targets.index', compact('kpis', 'roles', 'courses', 'targets', 'canManage'));
    }

    public function store(Request $request)
    {
        if (!$this->canEditTargets()) {
            return abort(401);
        }

        $validated = $request->validate([
            'kpi_id' => ['required', 'integer', Rule::exists('kpis', 'id')],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
            'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')],
            'target_value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        KpiTarget::query()->updateOrCreate(
            [
                'kpi_id' => $validated['kpi_id'],
                'role_id' => $validated['role_id'] ?? null,
                'course_id' => $validated['course_id'] ?? null,
            ],
            [
                'target_value' => (float) $validated['target_value'],
            ]
        );

        return redirect()->back()->with('flash_success', 'KPI target saved.');
    }

    public function destroy(KpiTarget $kpiTarget)
    {
        if (!$this->canEditTargets()) {
            return abort(401);
        }

        $kpiTarget->delete();

        return redirect()->back()->with('flash_success', 'KPI target removed.');
    }

    protected function canAccessTargets(): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || Gate::allows('kpi_target_access'));
    }

    protected function canEditTargets(): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || Gate::allows('kpi_target_edit'));
    }
}
