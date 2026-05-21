<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.employees') === true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['nullable', 'exists:departments,id'],

            'full_name_en' => ['required', 'string', 'max:255'],
            'full_name_ar' => ['required', 'string', 'max:255'],
            'national_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'national_id')->whereNull('deleted_at'),
            ],

            'gender' => ['required', 'in:male,female'],
            'date_of_joining' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,suspended,left'],

            'job_title' => ['nullable', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],

            'marital_status' => ['required', 'in:single,married'],
            'wife_working_status' => [
    'nullable',
    Rule::requiredIf(function () {
        return $this->input('gender') === 'male'
            && $this->input('marital_status') === 'married';
    }),
    'in:working,not_working',
],

            'salary' => ['required', 'array'],
            'salary.salary_mode' => ['required', 'in:bank_transfer,cash,cheques'],
            'salary.salary_value' => ['required', 'numeric', 'min:0'],

            'salary.bank_account_name' => [
                'nullable',
                'required_if:salary.salary_mode,bank_transfer',
                'string',
                'max:255',
            ],
            'salary.bank_account_number' => [
                'nullable',
                'required_if:salary.salary_mode,bank_transfer',
                'string',
                'max:50',
            ],
            'salary.iban' => [
                'nullable',
                'required_if:salary.salary_mode,bank_transfer',
                'string',
                'max:34',
            ],

            'salary.social_security_deduction' => ['nullable', 'numeric', 'min:0'],
            'salary.insurance_deduction' => ['nullable', 'numeric', 'min:0'],
            'salary.effective_from' => ['nullable', 'date'],
            'leave_balances' => ['nullable', 'array'],

'leave_balances.*.leave_type' => ['required_with:leave_balances', 'string', 'max:100'],
'leave_balances.*.name_ar' => ['required_with:leave_balances', 'string', 'max:255'],
'leave_balances.*.name_en' => ['required_with:leave_balances', 'string', 'max:255'],
'leave_balances.*.total_days' => ['required_with:leave_balances', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'array'],
            'allowances.*.name_ar' => ['required_with:allowances', 'string', 'max:255'],
            'allowances.*.name_en' => ['required_with:allowances', 'string', 'max:255'],
            'allowances.*.amount' => ['required_with:allowances', 'numeric', 'min:0'],

            'shift_ids' => ['nullable', 'array'],
            'shift_ids.*' => ['integer', 'exists:shifts,id'],

            'educations' => ['nullable', 'array'],
            'educations.*.school_university_ar' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.school_university_en' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.qualification_ar' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.qualification_en' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.level' => [
                'required_with:educations',
                'in:graduation,post_graduation,under_graduation',
            ],
            'educations.*.year_of_passing' => ['nullable', 'string', 'max:20'],
            'educations.*.class_percentage' => ['nullable', 'string', 'max:50'],
            'educations.*.major_ar' => ['nullable', 'string', 'max:255'],
            'educations.*.major_en' => ['nullable', 'string', 'max:255'],
        ];
    }
}