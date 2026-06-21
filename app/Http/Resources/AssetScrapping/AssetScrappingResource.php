<?php

namespace App\Http\Resources\AssetScrapping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetScrappingResource extends JsonResource
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

            'scrap_date' => $this->scrap_date?->format('Y-m-d'),

            'asset_cost' => $this->asset_cost,
            'accumulated_depreciation_amount' => $this->accumulated_depreciation_amount,
            'book_value_loss' => $this->book_value_loss,

            'fixed_asset_account_id' => $this->fixed_asset_account_id,
            'accumulated_depreciation_account_id' => $this->accumulated_depreciation_account_id,
            'loss_on_disposal_account_id' => $this->loss_on_disposal_account_id,

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