<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.users');
    }

    public function rules(): array
    {
        return [
            'national_id' => ['nullable', 'exists:employees,national_id'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&^_\-\.])[A-Za-z\d@$!%*#?&^_\-\.]+$/'
            ],
            'role' => ['nullable', 'exists:roles,name'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

   
}