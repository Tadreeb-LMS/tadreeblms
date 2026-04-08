<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKpiRequest extends FormRequest
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
        $kpiId = $this->route('kpi');

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z][A-Z0-9_]*$/',
                Rule::unique('kpis', 'code')->ignore($kpiId),
            ],
            'type' => ['required', Rule::in(array_keys(config('kpi.types', [])))],
            'weight' => 'required|numeric|min:0|max:' . config('kpi.max_weight', 100),
            'description' => 'required|string|max:5000',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'integer|exists:courses,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $kpiId = $this->route('kpi');
            if (!$kpiId) {
                return;
            }

            $kpi = \App\Models\Kpi::find($kpiId);
            if (!$kpi) {
                return;
            }

            $typeChanged = $kpi->type !== $this->input('type');
            if ($typeChanged && $kpi->is_active && (float) $this->input('weight', 0) <= 0) {
                $validator->errors()->add('weight', 'Weight must be greater than 0 when changing KPI type for an active KPI.');
            }
        });
    }

    public function messages()
    {
        return [
            'code.regex' => 'KPI code must start with an uppercase letter and use only uppercase letters, numbers, and underscores.',
            'type.in' => 'Selected KPI type is not supported.',
        ];
    }
}
