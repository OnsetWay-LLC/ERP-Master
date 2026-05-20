<?php

namespace App\Http\Requests\BankReconciliation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.bank_reconciliation');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'from_reference_date' => ['required', 'date'],
            'to_reference_date' => ['required', 'date', 'after_or_equal:from_reference_date'],

            'closing_balance' => ['required', 'numeric'],
        ];
    }
}