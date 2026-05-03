<?php

namespace App\Http\Requests\TaxTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.tax');
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_templates', 'title')
                    ->where('company_id', 1)
                    ->whereNull('deleted_at'),
            ],

            'lines' => ['required', 'array', 'min:1'],

            'lines.*.type' => ['required', 'in:on_net_total,actual'],

            'lines.*.account_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', 1)
                    ->where('account_type', 'tax')
                    ->whereNull('deleted_at'),
            ],

            'lines.*.tax_rate' => ['nullable', 'numeric'],
            'lines.*.amount' => ['nullable', 'numeric'],
        ];
    }
}

