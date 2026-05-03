<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.users');
    }

    public function rules(): array
    {
        return [
            'national_id' => ['required', 'exists:employees,national_id'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&^_\-\.])[A-Za-z\d@$!%*#?&^_\-\.]+$/'
            ],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'national_id.required' => 'National ID is required.',
            'national_id.exists' => 'Employee with this National ID was not found.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain letters, numbers, and at least one special character.',
            'role.required' => 'Role is required.',
            'role.exists' => 'Selected role does not exist.',
        ];
    }
}