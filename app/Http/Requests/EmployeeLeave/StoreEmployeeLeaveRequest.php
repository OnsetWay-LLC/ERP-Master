<?php

namespace App\Http\Requests\EmployeeLeave;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees') === true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],

            'leave_type' => ['required', 'string', 'max:100'],

            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],

            'description' => ['nullable', 'string'],

            'status' => ['required', 'in:approved,rejected'],

            'deduct_from_salary' => ['nullable', 'boolean'],
        ];
    }
}