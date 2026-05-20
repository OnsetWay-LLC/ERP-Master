<?php

namespace App\Http\Requests\FeesTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeesTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.fees_templates');
    }

    public function rules(): array
    {
        $id = $this->route('fees_template') ?? $this->route('id');
        return [
            'title' => [
    'sometimes',
    'required',
    'string',
    'max:255',
    Rule::unique('fees_templates', 'title')
        ->where('company_id', 1)
        ->whereNull('deleted_at')
        ->ignore($id),
],


            'type' => [
                'sometimes',
                'required',
                Rule::in(['percentage', 'fixed_amount']),
            ],

            'account_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'fees_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}