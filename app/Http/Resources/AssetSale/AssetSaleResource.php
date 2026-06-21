<?php

namespace App\Http\Resources\AssetSale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetSaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'asset_id' => $this->asset_id,
            'asset_series' => $this->asset?->series,
            'asset_name_ar' => $this->asset?->asset_name_ar,
            'asset_name_en' => $this->asset?->asset_name_en,
            'asset' => $this->whenLoaded('asset'),

            'asset_category_id' => $this->asset_category_id,
            'asset_category' => $this->whenLoaded('assetCategory'),

            'sell_qty' => $this->sell_qty,
            'asset_qty_before_sale' => $this->asset_qty_before_sale,
            'asset_qty_after_sale' => $this->asset_qty_after_sale,

            'posting_date' => $this->posting_date?->format('Y-m-d'),
            'posting_time' => $this->posting_time,

            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse'),

            'rate' => $this->rate,
            'sale_value' => $this->sale_value,

            'original_asset_cost' => $this->original_asset_cost,
            'accumulated_depreciation' => $this->accumulated_depreciation,
            'sold_asset_cost' => $this->sold_asset_cost,
            'sold_accumulated_depreciation' => $this->sold_accumulated_depreciation,
            'book_value' => $this->book_value,

            'profit_amount' => $this->profit_amount,
            'loss_amount' => $this->loss_amount,

            'payment_mode' => $this->payment_mode,
            'receivable_account_id' => $this->receivable_account_id,
            'cash_account_id' => $this->cash_account_id,
            'bank_account_id' => $this->bank_account_id,

            'fixed_asset_account_id' => $this->fixed_asset_account_id,
            'accumulated_depreciation_account_id' => $this->accumulated_depreciation_account_id,
            'gain_account_id' => $this->gain_account_id,
            'loss_account_id' => $this->loss_account_id,

            'sales_invoice_id' => $this->sales_invoice_id,
            'journal_entry_id' => $this->journal_entry_id,
            'journal_entry' => $this->whenLoaded('journalEntry'),

            'status' => $this->status,
            'created_by' => $this->created_by,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'submitted_by' => $this->submitted_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}