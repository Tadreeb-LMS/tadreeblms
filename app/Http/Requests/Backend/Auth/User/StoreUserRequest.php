<?php

namespace App\Http\Requests\Backend\Auth\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreUserRequest.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'     => ['required', 'max:191'],
            'last_name'  => ['required', 'max:191'],
            'email'    => ['required', 'email', 'max:191', Rule::unique('users')],
            'password' => ['required', 'min:6', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', Rule::exists('roles', 'name')],
            'department' => [
                'nullable',
                Rule::exists('department', 'id')->where(function ($query) {
                    $query->where('published', 1);
                }),
            ],
            'active' => ['nullable', 'boolean'],
            'confirmed' => ['nullable', 'boolean'],
            'return_to' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
