<?php
namespace App\Http\Requests\Employee;
use Illuminate\Foundation\Http\FormRequest;
class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees');
    }

   public function rules(): array
{
    return [
        'department_id' => ['nullable', 'exists:departments,id'],

        'full_name' => ['required', 'string'],
        'national_id' => ['required', 'unique:employees,national_id','integer'],

        'gender' => ['required', 'in:male,female'],
        'date_of_joining' => ['required', 'date'],
        'status' => ['required', 'in:active,inactive,suspended,left'],

        'mobile_number' => ['nullable'],
        'company_email' => ['nullable', 'email'],
        'address' => ['nullable'],

        'salary_mode' => ['required', 'in:bank_transfer,cash,cheques'],
        'salary_value' => ['required', 'numeric', 'min:0'],
        'marital_status' => ['nullable', 'in:single,married'],

       'shift_ids' => ['nullable', 'array'],
'shift_ids.*' => ['integer', 'exists:shifts,id'],
        

        'educations' => ['nullable', 'array'],
        'educations.*.school_university' => ['required_with:educations'],
        'educations.*.qualification' => ['required_with:educations'],
        'educations.*.level' => ['required_with:educations'],
        'educations.*.major_optional_subject' => ['nullable'],
         'educations.*.class_percentage' => ['nullable'],
        'educations.*.year_of_passing' => ['required_with:educations'],

    ];
}
}