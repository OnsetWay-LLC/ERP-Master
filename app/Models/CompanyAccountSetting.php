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
        'default_cogs_account_id',
        'default_discount_account_id',
        'default_accumulated_depreciation_account_id',
        'default_depreciation_expense_account_id',
        'default_asset_disposal_gain_loss_account_id',
        'default_inventory_account_id',
        'default_sales_tax_account_id',
        'default_purchase_tax_account_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}