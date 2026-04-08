<?php

namespace App\Http\Requests\Admin;

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
        return [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Z][A-Z0-9_]*$/', 'unique:kpis,code'],
            'type' => ['required', Rule::in(array_keys(config('kpi.types', [])))],
            'weight' => 'required|numeric|min:0|max:' . config('kpi.max_weight', 100),
            'description' => 'required|string|max:5000',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'integer|exists:courses,id',
        ];
    }

    public function messages()
    {
        return [
            'code.regex' => 'KPI code must start with an uppercase letter and use only uppercase letters, numbers, and underscores.',
            'type.in' => 'Selected KPI type is not supported.',
        ];
    }
}
