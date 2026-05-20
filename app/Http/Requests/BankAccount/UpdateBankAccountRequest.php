<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.bank_accounts');
    }

    public function rules(): array
    {
        $companyId = 1;
        $id = $this->route('id');

        return [
            'account_name_ar' => [
    'sometimes',
    'required',
    'string',
    'max:255',
],

'account_name_en' => [
    'sometimes',
    'required',
    'string',
    'max:255',
],

            'bank_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('banks', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'iban' => [
                'nullable',
                'string',
                'max:34',
                'regex:/^[A-Z]{2}[0-9A-Z]{13,32}$/',
                Rule::unique('bank_accounts', 'iban')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'branch_code' => ['nullable', 'string', 'max:50'],

            'bank_account_no' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'swift_code_bic' => [
                'nullable',
                'string',
                'max:11',
                'regex:/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            ],

            'account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_type', 'bank')
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}