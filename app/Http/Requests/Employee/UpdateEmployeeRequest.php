<?php
namespace App\Http\Requests\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees');
    }

   public function rules(): array
{
    return [
        'department_id' => ['nullable', 'exists:departments,id'],

        'full_name' => ['sometimes', 'string'],
        'national_id' => ['sometimes', 'unique:employees,national_id,' . $this->employee->id],

        'gender' => ['sometimes', 'in:male,female'],
        'date_of_joining' => ['sometimes', 'date'],
        'status' => ['sometimes', 'in:active,inactive,suspended,left'],

        'mobile_number' => ['nullable'],
        'company_email' => ['nullable', 'email'],
        'address' => ['nullable'],

        'salary_mode' => ['nullable', 'in:bank_transfer,cash,cheques'],
        'salary_value' => ['nullable'],
        'marital_status' => ['nullable', 'in:single,married'],

'shift_ids' => ['nullable', 'array'],
'shift_ids.*' => ['integer', 'exists:shifts,id'],
        'educations' => ['nullable', 'array'],
        'educations.*.school_university' => ['sometimes', 'string'],
        'educations.*.qualification' => ['sometimes', 'string'],
        'educations.*.level' => ['sometimes', 'string'],
        'educations.*.major_optional_subject' => ['nullable'],
         'educations.*.class_percentage' => ['nullable'],
        'educations.*.year_of_passing' => ['sometimes', 'string'],
    ];
}
}