<?php

namespace App\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use Illuminate\Support\Collection;

class KpiTemplateService
{
    /**
     * Apply a template by creating KPIs from template items.
     * Does not overwrite existing KPIs.
     */
    public function applyTemplate(KpiTemplate $template, $skipExisting = true)
    {
        $created = [];
        $skipped = [];

        foreach ($template->activeItems as $item) {
            $existingKpi = Kpi::where('code', $item->code)->first();

            if ($existingKpi && $skipExisting) {
                $skipped[] = [
                    'code' => $item->code,
                    'name' => $item->name,
                    'reason' => 'KPI with this code already exists',
                ];
                continue;
            }

            // Create new KPI from template item
            $kpi = Kpi::create([
                'name' => $item->name,
                'code' => $item->code,
                'description' => $item->description,
                'type' => $item->type,
                'weight' => $item->weight,
                'is_active' => $item->is_active,
            ]);

            $created[] = [
                'id' => $kpi->id,
                'code' => $kpi->code,
                'name' => $kpi->name,
                'weight' => $kpi->weight,
            ];
        }

        return [
            'template_id' => $template->id,
            'template_name' => $template->name,
            'created' => $created,
            'skipped' => $skipped,
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'success' => count($created) > 0,
        ];
    }

    /**
     * Get template preview with conflict detection.
     */
    public function previewTemplate(KpiTemplate $template)
    {
        $items = $template->activeItems()->get();
        $preview = [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category,
                'description' => $template->description,
                'use_case' => $template->use_case,
                'item_count' => $items->count(),
            ],
            'items' => [],
            'conflicts' => [],
            'conflicts_count' => 0,
        ];

        foreach ($items as $item) {
            $existing = Kpi::where('code', $item->code)->first();

            $itemPreview = [
                'code' => $item->code,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'weight' => $item->weight,
                'is_active' => $item->is_active,
                'status' => $existing ? 'conflict' : 'new',
            ];

            if ($existing) {
                $itemPreview['conflict_with'] = [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'weight' => $existing->weight,
                    'type' => $existing->type,
                ];
                $preview['conflicts'][] = $itemPreview;
                $preview['conflicts_count']++;
            } else {
                $preview['items'][] = $itemPreview;
            }
        }

        return $preview;
    }

    /**
     * Get all available templates grouped by category.
     */
    public function getTemplatesByCategory()
    {
        return KpiTemplate::where('is_active', true)
            ->get()
            ->groupBy('category');
    }

    /**
     * Get template statistics.
     */
    public function getTemplateStats(KpiTemplate $template)
    {
        $items = $template->activeItems()->get();
        $totalWeight = $items->sum('weight');

        return [
            'total_items' => $items->count(),
            'total_weight' => $totalWeight,
            'average_weight' => $items->count() > 0 ? $totalWeight / $items->count() : 0,
            'types' => $items->groupBy('type')->map->count(),
        ];
    }

    /**
     * Validate template before applying.
     */
    public function validateTemplate(KpiTemplate $template)
    {
        $errors = [];
        $items = $template->activeItems()->get();

        if ($items->isEmpty()) {
            $errors[] = 'Template has no active items.';
        }

        $codes = $items->pluck('code');
        if ($codes->count() !== $codes->unique()->count()) {
            $errors[] = 'Template contains duplicate KPI codes.';
        }

        $totalWeight = $items->sum('weight');
        if ($totalWeight <= 0) {
            $errors[] = 'Total weight must be greater than 0.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
