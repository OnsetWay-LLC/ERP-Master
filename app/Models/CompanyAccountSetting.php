<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAccountSetting extends Model
{
   protected $fillable = [
    'company_id',

    'default_bank_account_id',
    'default_cash_account_id',
    'default_receivable_account_id',
    'default_payable_account_id',

    'default_income_account_id',
    'default_direct_income_account_id',
    'default_indirect_income_account_id',

    'default_cogs_account_id',

    'default_direct_expense_account_id',
    'default_indirect_expense_account_id',

    'default_inventory_account_id',
    'default_payment_discount_account_id',
    'accumulated_depreciation_account_id',
    'depreciation_expense_account_id',
    'gain_loss_asset_disposal_account_id',
    'default_equity_account_id',
    'inventory_adjustment_account_id',
    'other_account_id',
];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}