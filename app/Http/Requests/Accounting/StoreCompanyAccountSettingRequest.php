<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyAccountSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.default_accounts');
    }

    public function rules(): array
    {
        return [
            'default_bank_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_cash_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_receivable_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_payable_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_direct_income_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_indirect_income_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_cogs_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_direct_expense_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_indirect_expense_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_inventory_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_sales_tax_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_purchase_tax_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],

            'default_payment_discount_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'accumulated_depreciation_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'depreciation_expense_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'gain_loss_asset_disposal_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'default_equity_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'inventory_adjustment_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'other_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
        ];
    }
}