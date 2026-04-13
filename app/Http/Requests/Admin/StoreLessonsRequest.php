<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Course;


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
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|array|min:1',
            'title.*' => 'required|string|max:255',
            'published' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'published' => (int) $this->boolean('published'),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $course = Course::find($this->course_id);

            if (!$course) {
                return;
            }

            $lessonStart = $this->start_date;
            $lessonEnd   = $this->end_date ?? $lessonStart;

            if ($lessonStart < $course->start_date || $lessonStart > $course->end_date) {
                $validator->errors()->add('start_date', 'Lesson start date must be within course duration.');
            }

            if ($lessonEnd < $course->start_date || $lessonEnd > $course->end_date) {
                $validator->errors()->add('end_date', 'Lesson end date must be within course duration.');
            }
        });
    }

}
