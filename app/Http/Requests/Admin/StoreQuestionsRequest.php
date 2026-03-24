<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionsRequest extends FormRequest
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
            'question' => 'required',
            'score' => ['required', 'integer', 'min:1', 'max:999'],        ];
    }

    public function messages()      
    {
        return [
            'score.required' => 'Marks is required.',
            'score.max' => 'Marks cannot exceed 999.',
            'score.min' => 'Marks must be at least 1.',
            'score.integer' => 'Marks must be a valid number.',
        ];
    }
}
