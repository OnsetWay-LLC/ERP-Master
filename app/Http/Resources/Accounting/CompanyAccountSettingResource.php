<?php
namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyAccountSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'default_bank_account_id' => $this->default_bank_account_id,
            'default_cash_account_id' => $this->default_cash_account_id,
            'default_receivable_account_id' => $this->default_receivable_account_id,
            'default_payable_account_id' => $this->default_payable_account_id,
            'default_income_account_id' => $this->default_income_account_id,
            'default_cogs_account_id' => $this->default_cogs_account_id,
            'default_discount_account_id' => $this->default_discount_account_id,
            'default_accumulated_depreciation_account_id' => $this->default_accumulated_depreciation_account_id,
            'default_depreciation_expense_account_id' => $this->default_depreciation_expense_account_id,
            'default_asset_disposal_gain_loss_account_id' => $this->default_asset_disposal_gain_loss_account_id,
            'default_inventory_account_id' => $this->default_inventory_account_id,
            'default_sales_tax_account_id' => $this->default_sales_tax_account_id,
            'default_purchase_tax_account_id' => $this->default_purchase_tax_account_id,
        ];
    }
}