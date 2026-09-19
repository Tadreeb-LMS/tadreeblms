<?php

namespace App\Services\Kpi;

use App\Models\Category;
use App\Models\Kpi;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class KpiCategoryConfigurationService
{
    public function activeWeightsByCategory(
        array $categoryIds = [],
        ?int $excludeKpiId = null
    ): array {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query = Kpi::query()
            ->where('is_active', true)
            ->with('categories:id');

        if ($excludeKpiId !== null) {
            $query->where('id', '!=', $excludeKpiId);
        }

        if (!empty($categoryIds)) {
            $query->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        $weights = [];

        foreach ($query->get() as $kpi) {
            foreach ($kpi->categories as $category) {
                $categoryId = (int) $category->id;
                if (!empty($categoryIds) && !in_array($categoryId, $categoryIds, true)) {
                    continue;
                }

                $weights[$categoryId] = ($weights[$categoryId] ?? 0)
                    + (float) $kpi->weight;
            }
        }

        return $weights;
    }

    public function projectedWeightsByCategory(
        array $categoryIds,
        float $proposedWeight,
        ?int $excludeKpiId = null
    ): array {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $currentWeights = $this->activeWeightsByCategory(
            $categoryIds,
            $excludeKpiId
        );

        $projectedWeights = [];

        foreach ($categoryIds as $categoryId) {
            $current = (float) ($currentWeights[$categoryId] ?? 0);

            $projectedWeights[$categoryId] =
                $current + $proposedWeight;
        }

        return $projectedWeights;
    }

        public function validateProjectedWeights(
        array $categoryIds,
        float $proposedWeight,
        ?int $excludeKpiId = null
    ): void {
        $target = (float) config(
            'kpi.total_weight_validation.target',
            100
        );

        $tolerance = max(
            0.0,
            (float) config(
                'kpi.total_weight_validation.tolerance',
                0.01
            )
        );

        $projectedWeights = $this->projectedWeightsByCategory(
            $categoryIds,
            $proposedWeight,
            $excludeKpiId
        );

        foreach ($projectedWeights as $categoryId => $total) {
            if ($total > ($target + $tolerance)) {
                throw ValidationException::withMessages([
                    'weight' => [
                        "The projected KPI weightage for category #{$categoryId} "
                        . "would be " . number_format($total, 2)
                        . "%, which exceeds the allowed "
                        . number_format($target, 2) . "%."
                    ],
                ]);
            }
        }
    }


    public function conflictingCategories(
        array $categoryIds,
        ?int $excludeKpiId = null
    ): Collection {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($categoryIds)) {
            return collect();
        }

        $target = (float) config(
            'kpi.total_weight_validation.target',
            100
        );

        $tolerance = max(
            0.0,
            (float) config(
                'kpi.total_weight_validation.tolerance',
                0.01
            )
        );

        $threshold = $target - $tolerance;

        $weights = $this->activeWeightsByCategory(
            $categoryIds,
            $excludeKpiId
        );

        $conflictingIds = collect($weights)
            ->filter(fn ($weight) => (float) $weight >= $threshold)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($conflictingIds->isEmpty()) {
            return collect();
        }

        $categories = Category::query()
            ->whereIn('id', $conflictingIds)
            ->pluck('name', 'id');

        return $conflictingIds->map(function ($categoryId) use ($weights, $categories) {
            return [
                'id' => $categoryId,
                'name' => $categories[$categoryId] ?? "Category #{$categoryId}",
                'weight' => (float) ($weights[$categoryId] ?? 0),
            ];
        });
    }

    public function assertNoConflictingCategories(
        array $categoryIds,
        ?int $excludeKpiId = null
    ): void {
        $conflicts = $this->conflictingCategories(
            $categoryIds,
            $excludeKpiId
        );

        if ($conflicts->isEmpty()) {
            return;
        }

        $messages = [];

        foreach ($conflicts as $category) {
            $messages[] = __('kpi.validation.category_configuration_complete', [
                'category' => $category['name'],
                'weight' => number_format($category['weight'], 2),
            ]);
        }

        throw ValidationException::withMessages([
            'category_ids' => $messages,
        ]);
    }

    public function lockCategories(array $categoryIds): void
    {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($categoryIds)) {
            return;
        }

        Category::query()
            ->whereIn('id', $categoryIds)
            ->lockForUpdate()
            ->get();
    }
}