<?php

namespace App\Http\Requests\TaxDeclarationSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxDeclarationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.tax_declaration_settings') === true;
        
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
}