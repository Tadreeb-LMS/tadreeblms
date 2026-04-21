<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKpiRequest;
use App\Http\Requests\Admin\UpdateKpiRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Kpi;
use App\Services\Kpi\KpiSnapshotService;
use App\Services\Kpi\KpiTypeCatalog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KpiController extends Controller
{
    protected $snapshotService;

    public function __construct(KpiSnapshotService $snapshotService)
    {
        $this->snapshotService = $snapshotService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $query = Kpi::query()->with('courses:id', 'categories:id');

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
        $weightInsights = $this->buildWeightInsights($totalActiveWeight);

        $calculatedKpis = $this->snapshotService->attachCalculations($allKpis, $totalActiveWeight);

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

        $kpiTypes = $this->getSupportedKpiTypes();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.kpis.partials.table', compact('kpis', 'kpiTypes'))->render(),
                'totalActiveWeight' => number_format($totalActiveWeight, 2),
            ]);
        }

        return view('backend.kpis.index', compact('kpis', 'kpiTypes', 'totalActiveWeight', 'weightInsights'));
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

        $kpiTypes = $this->getSupportedKpiTypes();
        $maxWeight = config('kpi.max_weight', 100);
        $defaultWeight = config('kpi.default_weight', 1);
        $activeTotalWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');
        $extremeWeightThreshold = (float) config('kpi.extreme_weight_warning_threshold', 70);
        $totalWeightValidation = config('kpi.total_weight_validation', []);
        $categories = Category::query()->orderBy('name')->select('id', 'name')->get();
        $courses = Course::query()->orderBy('title')->select('id', 'title', 'course_code')->get();

        return view('backend.kpis.create', compact('kpiTypes', 'maxWeight', 'defaultWeight', 'categories', 'courses', 'activeTotalWeight', 'extremeWeightThreshold', 'totalWeightValidation'));
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

        $kpi->categories()->sync($request->input('category_ids', []));
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
                    'category_ids' => $created->categories()->pluck('categories.id')->toArray(),
                    'course_ids' => $created->courses()->pluck('courses.id')->toArray(),
                ],
            ]);
        }

        $redirect = redirect()->route('admin.kpis.index')->withFlashSuccess('KPI created successfully.');

        $warnings = $this->buildPostSaveWeightWarnings($kpi);
        if (!empty($warnings)) {
            $redirect->with('flash_warning', implode(' ', $warnings));
        }

        return $redirect;
    }

    public function edit($kpi)
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        $kpi = Kpi::with('courses', 'categories')->findOrFail($kpi);

        $kpiTypes = $this->getSupportedKpiTypes();
        $maxWeight = config('kpi.max_weight', 100);
        $activeTotalWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');
        $extremeWeightThreshold = (float) config('kpi.extreme_weight_warning_threshold', 70);
        $totalWeightValidation = config('kpi.total_weight_validation', []);
        $categories = Category::query()->orderBy('name')->select('id', 'name')->get();
        $courses = Course::query()->orderBy('title')->select('id', 'title', 'course_code')->get();

        return view('backend.kpis.edit', compact('kpi', 'kpiTypes', 'maxWeight', 'categories', 'courses', 'activeTotalWeight', 'extremeWeightThreshold', 'totalWeightValidation'));
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
        $kpiModel->categories()->sync($request->input('category_ids', []));
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
                    'category_ids' => $kpiModel->categories()->pluck('categories.id')->toArray(),
                    'course_ids' => $kpiModel->courses()->pluck('courses.id')->toArray(),
                ],
            ]);
        }

        $redirect = redirect()->route('admin.kpis.index')->withFlashSuccess('KPI updated successfully.');

        $warnings = $this->buildPostSaveWeightWarnings($kpiModel);
        if (!empty($warnings)) {
            $redirect->with('flash_warning', implode(' ', $warnings));
        }

        return $redirect;
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
            'action' => 'archived',
            'previous_is_active' => $previousStatus,
            'new_is_active' => $previousStatus,
            'changed_by' => \Auth::id(),
            'meta' => ['soft_deleted' => true],
        ]);

        return redirect()->route('admin.kpis.index')->withFlashSuccess('KPI archived successfully.');
    }

    protected function buildWeightInsights(float $totalActiveWeight)
    {
        $target = (float) config('kpi.total_weight_validation.target', 100);
        $tolerance = max(0.0, (float) config('kpi.total_weight_validation.tolerance', 0.01));
        $extremeThreshold = (float) config('kpi.extreme_weight_warning_threshold', 70);

        $activeKpis = Kpi::query()->where('is_active', true)->get(['id', 'name', 'weight']);

        return [
            'target' => $target,
            'tolerance' => $tolerance,
            'validation_enabled' => (bool) config('kpi.total_weight_validation.enabled', false),
            'zero_weight_count' => $activeKpis->filter(function ($kpi) {
                return (float) $kpi->weight <= 0;
            })->count(),
            'extreme_weight_count' => $activeKpis->filter(function ($kpi) use ($extremeThreshold) {
                return (float) $kpi->weight >= $extremeThreshold;
            })->count(),
            'is_total_on_target' => abs($totalActiveWeight - $target) <= $tolerance,
        ];
    }

    protected function buildPostSaveWeightWarnings(Kpi $kpi)
    {
        $warnings = [];
        $totalActiveWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');
        $target = (float) config('kpi.total_weight_validation.target', 100);
        $tolerance = max(0.0, (float) config('kpi.total_weight_validation.tolerance', 0.01));
        $extremeThreshold = (float) config('kpi.extreme_weight_warning_threshold', 70);

        if ($kpi->is_active && (float) $kpi->weight >= $extremeThreshold) {
            $warnings[] = sprintf(
                'Warning: %s has weight %.2f, which is above the extreme configuration threshold (%.2f).',
                $kpi->name,
                (float) $kpi->weight,
                $extremeThreshold
            );
        }

        if ($totalActiveWeight <= 0) {
            $warnings[] = 'Warning: active KPI total weight is 0. Weighted scores will remain 0 until active weights are increased.';
        }

        if (!config('kpi.total_weight_validation.enabled', false) && abs($totalActiveWeight - $target) > $tolerance) {
            $warnings[] = sprintf(
                'Warning: active KPI total weight is %.2f (target %.2f). Consider rebalancing weights for more predictable distribution.',
                $totalActiveWeight,
                $target
            );
        }

        return $warnings;
    }

    /**
     * @return array<string, array>
     */
    protected function getSupportedKpiTypes(): array
    {
        return app(KpiTypeCatalog::class)->getSupportedOptions();
    }
}