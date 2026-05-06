<?php

namespace App\Http\Requests\Admin;

use App\Models\Kpi;
use App\Services\Kpi\KpiTypeCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKpiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $supportedTypes = app(KpiTypeCatalog::class)->getSupportedKeys();

        return [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Z][A-Z0-9_]*$/', 'unique:kpis,code'],
            'type' => ['required', Rule::in($supportedTypes)],
            'weight' => 'required|numeric|min:0|max:' . config('kpi.max_weight', 100),
            'description' => 'required|string|max:5000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'integer|exists:courses,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!config('kpi.total_weight_validation.enabled', false)) {
                return;
            }

            $proposedWeight = max(0.0, (float) $this->input('weight', 0));
            $currentActiveTotal = (float) Kpi::query()->where('is_active', true)->sum('weight');
            $projectedTotal = $currentActiveTotal + $proposedWeight;

            $target = (float) config('kpi.total_weight_validation.target', 100);
            $tolerance = max(0.0, (float) config('kpi.total_weight_validation.tolerance', 0.01));

            if (abs($projectedTotal - $target) > $tolerance) {
                $validator->errors()->add(
                    'weight',
                    __('kpi.validation.projected_total', [
                        'total' => number_format($projectedTotal, 2),
                        'tolerance' => number_format($tolerance, 2),
                        'target' => number_format($target, 2),
                    ])
                );
            }
        });
    }

    public function messages()
    {
        return [
            'code.regex' => __('kpi.validation.code_regex'),
            'type.in' => __('kpi.validation.type_in'),
        ];
    }
}
