<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoursesRequest extends FormRequest
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
            
            'teachers.*' => 'exists:users,id',
            'title' => 'required|max:200',
            'category_id' => 'required|exists:categories,id',
            'department_id' => 'nullable',
            'include_in_kpi' => 'required|boolean',
            'start_date' => 'nullable|date_format:'.config('app.date_format'),
            'course_code' => 'required|max:100|unique:courses,course_code,'.$this->route('course'),
            'course_type' => 'required'
            //'arabic_title' => 'required|max:200',
        ];
    }
}
