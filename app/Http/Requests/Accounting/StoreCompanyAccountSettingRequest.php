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
            'default_bank_account_id' => ['nullable', 'integer'],
            'default_cash_account_id' => ['nullable', 'integer'],
            'default_receivable_account_id' => ['nullable', 'integer'],
            'default_payable_account_id' => ['nullable', 'integer'],
            'default_income_account_id' => ['nullable', 'integer'],
            'default_cogs_account_id' => ['nullable', 'integer'],
            'default_discount_account_id' => ['nullable', 'integer'],
            'default_accumulated_depreciation_account_id' => ['nullable', 'integer'],
            'default_depreciation_expense_account_id' => ['nullable', 'integer'],
            'default_asset_disposal_gain_loss_account_id' => ['nullable', 'integer'],
            'default_inventory_account_id' => ['nullable', 'integer'],
            'default_sales_tax_account_id' => ['nullable', 'integer'],
            'default_purchase_tax_account_id' => ['nullable', 'integer'],
        ];
    }
}