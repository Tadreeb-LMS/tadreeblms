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
        'title' => 'required|string|max:255',
        'course_type' => 'required|string',

        // Conditional Validation
        'start_date' => [
            'nullable',
            'required_unless:course_type,Online',
            'date'
        ],

        'end_date' => [
            'nullable',
            'required_unless:course_type,Online',
            'date',
            'after_or_equal:start_date'
        ],
    ];
}
}
