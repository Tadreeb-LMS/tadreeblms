<?php

namespace App\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiDashboardSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class KpiDashboardConfigService
{
    public const SCOPE = 'global';
    public const PRESENTATION_COMPACT = 'compact';
    public const PRESENTATION_DETAIL = 'detail';

    protected $snapshotService;

    public function __construct(KpiSnapshotService $snapshotService)
    {
        $this->snapshotService = $snapshotService;
    }

    public function getEditableConfiguration(): array
    {
        $setting = $this->ensureDefaultConfiguration();
        $widgets = collect($setting ? ($setting->widgets ?? []) : []);
        $widgetMap = $widgets->keyBy(function (array $widget) {
            return (int) ($widget['kpi_id'] ?? 0);
        });

        $activeKpis = Kpi::query()
            ->where('is_active', true)
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'weight']);

        return [
            'setting' => $setting,
            'active_kpis' => $activeKpis,
            'selected_ids' => $widgets->pluck('kpi_id')->map(function ($id) {
                return (int) $id;
            })->all(),
            'widget_map' => $widgetMap,
        ];
    }

    public function updateConfiguration(array $selectedKpiIds, array $presentations, array $displayOrder, ?int $actorId = null): KpiDashboardSetting
    {
        $selectedKpiIds = collect($selectedKpiIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values();

        $validIds = Kpi::query()
            ->where('is_active', true)
            ->whereIn('id', $selectedKpiIds->all())
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $widgets = collect($validIds)
            ->map(function (int $kpiId, int $index) use ($presentations, $displayOrder) {
                $presentation = (string) ($presentations[$kpiId] ?? self::PRESENTATION_COMPACT);
                if (!in_array($presentation, [self::PRESENTATION_COMPACT, self::PRESENTATION_DETAIL], true)) {
                    $presentation = self::PRESENTATION_COMPACT;
                }

                return [
                    'kpi_id' => $kpiId,
                    'presentation' => $presentation,
                    'display_order' => max(1, (int) ($displayOrder[$kpiId] ?? ($index + 1))),
                ];
            })
            ->sortBy([
                ['display_order', 'asc'],
                ['kpi_id', 'asc'],
            ])
            ->values()
            ->all();

        $setting = $this->tableExists()
            ? KpiDashboardSetting::query()->firstOrNew(['scope' => self::SCOPE])
            : new KpiDashboardSetting(['scope' => self::SCOPE]);

        $setting->widgets = $widgets;

        if (!$setting->exists) {
            $setting->created_by = $actorId;
        }

        $setting->updated_by = $actorId;
        $setting->save();

        return $setting;
    }

    public function getDashboardWidgets(): array
    {
        $setting = $this->ensureDefaultConfiguration();
        $configuredWidgets = collect($setting ? ($setting->widgets ?? []) : []);
        $selectedIds = $configuredWidgets->pluck('kpi_id')->map(function ($id) {
            return (int) $id;
        })->filter()->values()->all();

        if (empty($selectedIds)) {
            return [
                'widgets' => [],
                'setting' => $setting,
            ];
        }

        $activeKpis = Kpi::query()
            ->where('is_active', true)
            ->whereIn('id', $selectedIds)
            ->with('categories:id,name', 'courses:id', 'currentSnapshot')
            ->get()
            ->keyBy('id');

        if ($activeKpis->isEmpty()) {
            return [
                'widgets' => [],
                'setting' => $setting,
            ];
        }

        $totalActiveWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');
        $calculatedKpis = $this->snapshotService
            ->attachCalculations($activeKpis->values(), $totalActiveWeight)
            ->keyBy('id');

        $widgets = $configuredWidgets
            ->map(function (array $widget) use ($calculatedKpis) {
                $kpiId = (int) ($widget['kpi_id'] ?? 0);
                $kpi = $calculatedKpis->get($kpiId);
                if (!$kpi) {
                    return null;
                }

                $presentation = (string) ($widget['presentation'] ?? self::PRESENTATION_COMPACT);
                if (!in_array($presentation, [self::PRESENTATION_COMPACT, self::PRESENTATION_DETAIL], true)) {
                    $presentation = self::PRESENTATION_COMPACT;
                }

                $kpi->dashboard_presentation = $presentation;
                $kpi->dashboard_display_order = (int) ($widget['display_order'] ?? 0);

                return $kpi;
            })
            ->filter()
            ->values();

        return [
            'widgets' => $widgets,
            'setting' => $setting,
        ];
    }

    protected function ensureDefaultConfiguration(): ?KpiDashboardSetting
    {
        if (!$this->tableExists()) {
            return null;
        }

        $setting = KpiDashboardSetting::query()->firstOrNew(['scope' => self::SCOPE]);
        if ($setting->exists && is_array($setting->widgets) && !empty($setting->widgets)) {
            return $setting;
        }

        $defaultIds = Kpi::query()
            ->where('is_active', true)
            ->orderByDesc('weight')
            ->orderBy('name')
            ->limit(4)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $setting->widgets = collect($defaultIds)
            ->map(function (int $id, int $index) {
                return [
                    'kpi_id' => $id,
                    'presentation' => $index === 0 ? self::PRESENTATION_DETAIL : self::PRESENTATION_COMPACT,
                    'display_order' => $index + 1,
                ];
            })
            ->all();

        $setting->save();

        return $setting;
    }

    protected function tableExists(): bool
    {
        return Schema::hasTable('kpi_dashboard_settings');
    }
}
