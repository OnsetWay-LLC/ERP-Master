<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees') === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'status' => ['nullable', 'in:active,inactive,suspended,left'],
            'gender' => ['nullable', 'in:male,female'],
            'salary_mode' => ['nullable', 'in:bank_transfer,cash,cheques'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}