<?php

namespace App\Http\Resources\PurchaseInvoice;

use App\Models\CompanyAccountSetting;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseInvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        $settings = CompanyAccountSetting::where('company_id', $this->company_id)->first();

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'purchase_receipt_id' => $this->purchase_receipt_id,
            'purchase_order_id' => $this->purchase_order_id,
            'invoice_type' => $this->invoice_type,
            'supplier_id' => $this->supplier_id,

            'posting_date' => $this->posting_date,
            'posting_time' => $this->posting_time,
            'due_date' => $this->due_date,

            'supplier_invoice_no' => $this->supplier_invoice_no,
            'supplier_invoice_date' => $this->supplier_invoice_date,

            'posting_method' => $this->posting_method,

            'supplier_payable_account_id' => $this->supplier_payable_account_id,
            'fixed_asset_account_id' => $this->purchase_account_id,
            'discount_account_id' => $settings?->default_indirect_income_account_id,

            'net_total' => (float) $this->net_total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'discount_amount' => (float) $this->discount_amount,
            'grand_total' => (float) $this->grand_total,
            'paid_amount' => (float) $this->paid_amount,
            'outstanding_amount' => (float) $this->outstanding_amount,

            'status' => $this->status,
            'journal_entry_id' => $this->journal_entry_id,

            'accounts_source' => [
                'fixed_asset_account_id' => 'asset_item.asset_category.fixed_asset_account_id',
                'discount_account_id' => 'company_account_settings.default_indirect_income_account_id',
                'supplier_payable_account_id' => 'request.supplier_payable_account_id',
            ],

            'supplier' => $this->whenLoaded('supplier'),
            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),
        ];
    }
}