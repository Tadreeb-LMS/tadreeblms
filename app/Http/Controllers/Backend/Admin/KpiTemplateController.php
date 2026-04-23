<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use App\Services\Kpi\KpiTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KpiTemplateController extends Controller
{
    protected $templateService;

    public function __construct(KpiTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Show template gallery.
     * GET /admin/kpi-templates
     */
    public function index()
    {
        if (!$this->canAccessTemplates()) {
            return abort(401);
        }

        $templates = $this->templateService->getTemplatesByCategory();
        $canApply = $this->canApplyTemplates();
        $canCreate = $this->canCreateTemplates();

        return view('backend.kpis.templates.index', compact('templates', 'canApply', 'canCreate'));
    }

    /**
     * Show dedicated template creation page.
     * GET /admin/kpi-templates/create
     */
    public function create()
    {
        if (!$this->canCreateTemplates()) {
            return abort(401);
        }

        $kpis = Kpi::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'weight', 'description', 'is_active']);

        return view('backend.kpis.templates.create', compact('kpis'));
    }

    /**
     * Store a custom template with items.
     * POST /admin/kpi-templates
     */
    public function store(Request $request)
    {
        if (!$this->canCreateTemplates()) {
            return abort(401);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('kpi_templates', 'slug')],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'use_case' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.code' => ['nullable', 'string', 'max:64'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.type' => ['nullable', 'string', 'max:100'],
            'items.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.is_active' => ['nullable', 'boolean'],
            'existing_kpi_ids' => ['nullable', 'array'],
            'existing_kpi_ids.*' => ['integer', Rule::exists('kpis', 'id')],
        ]);

        $normalizedItems = collect($validated['items'] ?? [])
            ->filter(function ($item) {
                return !empty(trim((string) ($item['name'] ?? ''))) && !empty(trim((string) ($item['code'] ?? ''))) && !empty(trim((string) ($item['type'] ?? ''))) && isset($item['weight']) && $item['weight'] !== '';
            })
            ->map(function ($item) {
                return [
                    'name' => trim((string) $item['name']),
                    'code' => strtoupper(trim((string) $item['code'])),
                    'description' => $item['description'] ?? null,
                    'type' => trim((string) $item['type']),
                    'weight' => (float) $item['weight'],
                    'is_active' => isset($item['is_active']) ? (bool) $item['is_active'] : true,
                ];
            })
            ->values();

        $selectedKpis = Kpi::query()
            ->whereIn('id', $validated['existing_kpi_ids'] ?? [])
            ->get();

        $selectedKpiItems = $selectedKpis->map(function ($kpi) {
            return [
                'name' => $kpi->name,
                'code' => strtoupper($kpi->code),
                'description' => $kpi->description,
                'type' => $kpi->type,
                'weight' => (float) $kpi->weight,
                'is_active' => (bool) $kpi->is_active,
            ];
        });

        $allItems = $normalizedItems
            ->concat($selectedKpiItems)
            ->unique('code')
            ->values();

        if ($allItems->isEmpty()) {
            return redirect()->back()->withInput()->with('flash_danger', 'Please add at least one KPI item or select existing KPIs.');
        }

        $normalizedCodes = $allItems->pluck('code')->map(function ($code) {
            return strtoupper(trim((string) $code));
        });

        if ($normalizedCodes->count() !== $normalizedCodes->unique()->count()) {
            return redirect()->back()->withInput()->with('flash_danger', 'Template item codes must be unique within the template.');
        }

        $template = KpiTemplate::query()->create([
            'name' => trim($validated['name']),
            'slug' => $validated['slug'] ?? null,
            'category' => trim($validated['category']),
            'description' => $validated['description'] ?? null,
            'use_case' => $validated['use_case'] ?? null,
            'is_active' => true,
        ]);

        foreach ($allItems as $index => $item) {
            KpiTemplateItem::query()->create([
                'template_id' => $template->id,
                'name' => $item['name'],
                'code' => $item['code'],
                'description' => $item['description'] ?? null,
                'type' => $item['type'],
                'weight' => $item['weight'],
                'is_active' => $item['is_active'],
                'sort_order' => $index + 1,
            ]);
        }

        $template->update(['item_count' => $template->items()->count()]);

        return redirect()->route('admin.kpi-templates.show', $template->id)->with('flash_success', 'Template created successfully. You can now preview and apply it.');
    }

    /**
     * Show template detail with preview.
     * GET /admin/kpi-templates/{kpiTemplate}
     */
    public function show(KpiTemplate $kpiTemplate)
    {
        if (!$this->canAccessTemplates()) {
            return abort(401);
        }

        $preview = $this->templateService->previewTemplate($kpiTemplate);
        $stats = $this->templateService->getTemplateStats($kpiTemplate);
        $validation = $this->templateService->validateTemplate($kpiTemplate);
        $canApply = $this->canApplyTemplates();

        return view('backend.kpis.templates.show', ['template' => $kpiTemplate, 'preview' => $preview, 'stats' => $stats, 'validation' => $validation, 'canApply' => $canApply]);
    }

    /**
     * Apply a template and create KPIs.
     * POST /admin/kpi-templates/{kpiTemplate}/apply
     */
    public function apply(Request $request, KpiTemplate $kpiTemplate)
    {
        if (!$this->canApplyTemplates()) {
            return abort(401);
        }

        $skipExisting = $request->boolean('skip_existing', true);
        $result = $this->templateService->applyTemplate($kpiTemplate, $skipExisting);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            $message = sprintf(
                'Template "%s" applied successfully. %d KPI(s) created.',
                $kpiTemplate->name,
                $result['created_count']
            );

            if ($result['skipped_count'] > 0) {
                $message .= sprintf(' %d KPI(s) skipped (already exist).', $result['skipped_count']);
            }

            return redirect()->route('admin.kpis.index')->with('flash_success', $message);
        }

        $message = 'No new KPIs were created. All template items may already exist.';

        return redirect()->back()->with('flash_danger', $message);
    }

    /**
     * Check access to template gallery.
     */
    protected function canAccessTemplates(): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || Gate::allows('kpi_template_access'));
    }

    /**
     * Check ability to apply templates.
     */
    protected function canApplyTemplates(): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || Gate::allows('kpi_template_edit'));
    }

    /**
     * Check ability to create templates.
     */
    protected function canCreateTemplates(): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || Gate::allows('kpi_template_create'));
    }
}
