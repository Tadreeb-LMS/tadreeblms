<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Course;
use Carbon\Carbon;

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
            'published' => 'nullable|boolean',
            'lesson_start_date' => 'required|array|min:1',
            'lesson_start_date.*' => 'nullable|date',
            'expire_at' => 'nullable|date|after_or_equal:start_date',
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

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $course = Course::find($this->course_id);

        if (!$course) {
            return;
        }

        $courseStart = Carbon::parse($course->start_date);
        $courseEnd   = Carbon::parse($course->expire_at);

        $dates = $this->lesson_start_date ?? [];

        foreach ($dates as $index => $date) {

            if (!$date) continue;

            try {
                $lessonDate = Carbon::parse($date);
            } catch (\Exception $e) {
                $validator->errors()->add(
                    "lesson_start_date.$index",
                    "Invalid date format."
                );
                continue;
            }

            if ($lessonDate->lt($courseStart) || $lessonDate->gt($courseEnd)) {
                $validator->errors()->add(
                    "lesson_start_date.$index",
                    "Lesson date must be between {$course->start_date} and {$course->expire_at}"
                );
            }
        }
    });
}
}
