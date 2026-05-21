<?php

namespace App\Http\Requests\PayrollTaxSetting;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollTaxSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.payroll_tax_settings') === true;
    }

    public function rules(): array
    {
        return [
            'single_or_working_wife_exemption' => ['required', 'numeric', 'min:0'],
            'married_not_working_wife_exemption' => ['required', 'numeric', 'min:0'],

            'brackets' => ['required', 'array', 'min:1'],

            'brackets.*.from_amount' => ['required', 'numeric', 'min:0'],
            'brackets.*.to_amount' => ['nullable', 'numeric', 'gt:brackets.*.from_amount'],
            'brackets.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'brackets.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}