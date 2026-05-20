<?php

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyAccountSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'default_bank_account_id' => $this->default_bank_account_id,
            'default_cash_account_id' => $this->default_cash_account_id,
            'default_receivable_account_id' => $this->default_receivable_account_id,
            'default_payable_account_id' => $this->default_payable_account_id,
            'default_direct_income_account_id' => $this->default_direct_income_account_id,
            'default_indirect_income_account_id' => $this->default_indirect_income_account_id,
            'default_cogs_account_id' => $this->default_cogs_account_id,
            'default_direct_expense_account_id' => $this->default_direct_expense_account_id,
            'default_indirect_expense_account_id' => $this->default_indirect_expense_account_id,
            'default_inventory_account_id' => $this->default_inventory_account_id,
            'default_payment_discount_account_id' => $this->default_payment_discount_account_id,
            'gain_loss_asset_disposal_account_id' => $this->gain_loss_asset_disposal_account_id,
            'default_equity_account_id' => $this->default_equity_account_id,
            'inventory_adjustment_account_id' => $this->inventory_adjustment_account_id,
            'other_account_id' => $this->other_account_id,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}