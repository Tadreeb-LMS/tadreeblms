<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonsRequest extends FormRequest
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
        $rules = [
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|array|min:1',
            'title.*' => 'required|string|max:255',
        ];

        if (is_array($this->input('published'))) {
            $rules['published'] = 'nullable|array';
            $rules['published.*'] = 'boolean';
        } else {
            $rules['published'] = 'nullable|boolean';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if (is_array($this->input('published'))) {
            $published = array_map(function ($value) {
                return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }, $this->input('published', []));

            $this->merge([
                'published' => $published,
            ]);

            return;
        }

        $this->merge([
            'published' => (int) $this->boolean('published'),
        ]);
    }
}