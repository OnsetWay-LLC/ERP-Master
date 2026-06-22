<?php

namespace App\Http\Requests\TaxDeclarationSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxDeclarationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check()
            && auth('api')->user()->can('screen.tax_declaration_settings');
    }

    public function rules(): array
    {
        return [
            'report_month_type' => [
                'required',
                Rule::in(['odd', 'even']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'report_month_type.required' => 'The report month type is required.',
            'report_month_type.in' => 'The report month type must be either odd or even.',
        ];
    }
}