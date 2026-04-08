<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKpiRequest;
use App\Http\Requests\Admin\UpdateKpiRequest;
use App\Models\Course;
use App\Models\Kpi;
use App\Services\KpiCalculationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KpiController extends Controller
{
    protected $calculationService;

    public function __construct(KpiCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $query = Kpi::query()->with('courses:id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $sorts = $this->normalizeSorts($request);
        $allKpis = $query->get();
        $totalActiveWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');

        $calculatedKpis = $allKpis->map(function ($kpi) use ($totalActiveWeight) {
            $kpi->calculation = $this->calculationService->calculateForKpi($kpi, $totalActiveWeight);
            return $kpi;
        });

        $sorted = $calculatedKpis->sort(function ($left, $right) use ($sorts) {
            foreach ($sorts as $sort) {
                $comparison = $this->compareKpisBySort($left, $right, $sort['by'], $sort['dir']);
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        })->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $pageItems = $sorted->forPage($currentPage, $perPage)->values();

        $kpis = new LengthAwarePaginator(
            $pageItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $kpis->appends($request->query());

        $kpiTypes = config('kpi.types', []);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.kpis.partials.table', compact('kpis', 'kpiTypes'))->render(),
                'totalActiveWeight' => number_format($totalActiveWeight, 2),
            ]);
        }

        return view('backend.kpis.index', compact('kpis', 'kpiTypes', 'totalActiveWeight'));
    }

    protected function normalizeSorts(Request $request)
    {
        $sortBy = $request->input('sort_by', []);
        $sortDir = $request->input('sort_dir', []);

        if (!is_array($sortBy)) {
            $sortBy = [$sortBy];
        }

        if (!is_array($sortDir)) {
            $sortDir = [$sortDir];
        }

        $allowed = ['created_at', 'name', 'code', 'type', 'weight', 'is_active', 'current_value', 'weighted_score'];
        $sorts = [];

        foreach ($sortBy as $index => $column) {
            if (!in_array($column, $allowed, true)) {
                continue;
            }

            $direction = strtolower($sortDir[$index] ?? 'asc');
            $sorts[] = [
                'by' => $column,
                'dir' => $direction === 'desc' ? 'desc' : 'asc',
            ];
        }

        if (empty($sorts)) {
            $sorts[] = ['by' => 'created_at', 'dir' => 'desc'];
        }

        return $sorts;
    }

    protected function compareKpisBySort($left, $right, $column, $direction)
    {
        $leftValue = $this->extractSortValue($left, $column);
        $rightValue = $this->extractSortValue($right, $column);

        if (in_array($column, ['current_value', 'weighted_score'], true)) {
            $leftExcluded = (bool) ($left->calculation['excluded'] ?? false);
            $rightExcluded = (bool) ($right->calculation['excluded'] ?? false);

            if ($leftExcluded !== $rightExcluded) {
                return $leftExcluded ? 1 : -1;
            }
        }

        if ($leftValue === $rightValue) {
            return 0;
        }

        if ($leftValue < $rightValue) {
            return $direction === 'asc' ? -1 : 1;
        }

        return $direction === 'asc' ? 1 : -1;
    }

    protected function extractSortValue($kpi, $column)
    {
        switch ($column) {
            case 'name':
            case 'code':
            case 'type':
                return mb_strtolower((string) $kpi->{$column});
            case 'weight':
                return (float) $kpi->weight;
            case 'is_active':
                return (int) $kpi->is_active;
            case 'current_value':
                return (float) ($kpi->calculation['value'] ?? 0);
            case 'weighted_score':
                return (float) ($kpi->calculation['weighted_score'] ?? 0);
            case 'created_at':
            default:
                return optional($kpi->created_at)->timestamp ?? 0;
        }
    }

    public function create()
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpiTypes = config('kpi.types', []);
        $maxWeight = config('kpi.max_weight', 100);
        $defaultWeight = config('kpi.default_weight', 1);
        $courses = Course::query()->orderBy('title')->select('id', 'title', 'course_code')->get();

        return view('backend.kpis.create', compact('kpiTypes', 'maxWeight', 'defaultWeight', 'courses'));
    }

    public function store(StoreKpiRequest $request)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpi = Kpi::create([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'type' => $request->type,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => true,
            'created_by' => \Auth::id(),
            'updated_by' => \Auth::id(),
        ]);

        $kpi->courses()->sync($request->input('course_ids', []));

        $created = Kpi::query()->where('code', strtoupper(trim($request->code)))->first();
        if ($created) {
            $created->statusHistories()->create([
                'action' => 'created',
                'previous_is_active' => null,
                'new_is_active' => true,
                'changed_by' => \Auth::id(),
                'meta' => [
                    'type' => $created->type,
                    'weight' => $created->weight,
                    'course_ids' => $created->courses()->pluck('courses.id')->toArray(),
                ],
            ]);
        }

        return redirect()->route('admin.kpis.index')->withFlashSuccess('KPI created successfully.');
    }

    public function edit($kpi)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpi = Kpi::with('courses')->findOrFail($kpi);

        $kpiTypes = config('kpi.types', []);
        $maxWeight = config('kpi.max_weight', 100);
        $courses = Course::query()->orderBy('title')->select('id', 'title', 'course_code')->get();

        return view('backend.kpis.edit', compact('kpi', 'kpiTypes', 'maxWeight', 'courses'));
    }

    public function update(UpdateKpiRequest $request, $kpi)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpiModel = Kpi::findOrFail($kpi);
        $oldType = $kpiModel->type;
        $oldWeight = $kpiModel->weight;

        $kpiModel->name = $request->name;
        $kpiModel->code = strtoupper(trim($request->code));
        $kpiModel->type = $request->type;
        $kpiModel->description = $request->description;
        $kpiModel->weight = $request->weight;
        $kpiModel->updated_by = \Auth::id();
        $kpiModel->save();
        $kpiModel->courses()->sync($request->input('course_ids', []));

        $typeChanged = $oldType !== $kpiModel->type;
        $weightChanged = (float) $oldWeight !== (float) $kpiModel->weight;
        if ($typeChanged || $weightChanged) {
            $kpiModel->statusHistories()->create([
                'action' => 'updated',
                'previous_is_active' => $kpiModel->is_active,
                'new_is_active' => $kpiModel->is_active,
                'changed_by' => \Auth::id(),
                'meta' => [
                    'old_type' => $oldType,
                    'new_type' => $kpiModel->type,
                    'old_weight' => $oldWeight,
                    'new_weight' => $kpiModel->weight,
                    'course_ids' => $kpiModel->courses()->pluck('courses.id')->toArray(),
                ],
            ]);
        }

        return redirect()->route('admin.kpis.index')->withFlashSuccess('KPI updated successfully.');
    }

    public function toggleStatus($kpi)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpiModel = Kpi::findOrFail($kpi);
        $previousStatus = (bool) $kpiModel->is_active;
        $kpiModel->is_active = !$kpiModel->is_active;
        $kpiModel->updated_by = \Auth::id();
        $kpiModel->save();

        $kpiModel->statusHistories()->create([
            'action' => $kpiModel->is_active ? 'activated' : 'deactivated',
            'previous_is_active' => $previousStatus,
            'new_is_active' => (bool) $kpiModel->is_active,
            'changed_by' => \Auth::id(),
            'meta' => null,
        ]);

        return redirect()->route('admin.kpis.index')->withFlashSuccess('KPI status updated successfully.');
    }

    public function destroy($kpi)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpiModel = Kpi::findOrFail($kpi);
        $previousStatus = (bool) $kpiModel->is_active;
        $kpiModel->updated_by = \Auth::id();
        $kpiModel->save();
        $kpiModel->delete();

        $kpiModel->statusHistories()->create([
            'action' => 'deleted',
            'previous_is_active' => $previousStatus,
            'new_is_active' => $previousStatus,
            'changed_by' => \Auth::id(),
            'meta' => ['soft_deleted' => true],
        ]);

        return redirect()->route('admin.kpis.index')->withFlashSuccess('KPI deleted successfully.');
    }
}
