<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'full_name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:100', 'unique:users,username'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'phone' => ['required', 'string', 'max:20'],
        'password' => [
            'required',
            'string',
            'confirmed',
            'min:8',
            'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&^_\-\.])[A-Za-z\d@$!%*#?&^_\-\.]+$/'
        ],
    ];
}

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already taken.',
            'phone.required' => 'Phone number is required.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain letters, numbers, and at least one special character.',
        ];
    }
}