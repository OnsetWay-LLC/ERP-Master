<?php
namespace App\Http\Requests\Employee;
use Illuminate\Foundation\Http\FormRequest;
class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['nullable', 'in:active,inactive,suspended,left'],
            'gender' => ['nullable', 'in:male,female'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}