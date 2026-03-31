<?php

namespace App\Http\Requests\Backend\Auth\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateUserRequest.
 */
class UpdateUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:191'],
            'first_name'  => ['required', 'max:191'],
            'last_name'  => ['required', 'max:191'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', Rule::exists('roles', 'name')],
            'change_password' => ['nullable', 'boolean'],
            'password'   => ['nullable', 'required_if:change_password,1', 'min:6', 'confirmed'],
            'department' => [
                'nullable',
                Rule::exists('department', 'id')->where(function ($query) {
                    $query->where('published', 1);
                }),
            ],
        ];
    }
}
