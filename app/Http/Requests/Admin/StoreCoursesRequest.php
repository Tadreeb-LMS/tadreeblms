<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoursesRequest extends FormRequest
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
            'internalStudents.*' => 'exists:users,id',
            'externalStudents.*' => 'exists:users,id',
            'title' => 'required|max:200',
            'category_id' => 'required',
            'course_code' => 'required|max:100',
            //'arabic_title' => 'required|max:200',
            // 'marks_required' => 'required',
            //'start_date' => 'date_format:'.config('app.date_format'),
        ];
    }
}
