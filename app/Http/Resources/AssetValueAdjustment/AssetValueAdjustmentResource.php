<?php

namespace App\Http\Resources\AssetValueAdjustment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetValueAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'asset_id' => $this->asset_id,
            'asset' => $this->whenLoaded('asset'),

            'asset_category_id' => $this->asset_category_id,
            'asset_category' => $this->whenLoaded('assetCategory'),

            'posting_date' => $this->posting_date?->format('Y-m-d'),
            'finance_book' => $this->finance_book,

            'current_asset_value' => $this->current_asset_value,
            'new_asset_value' => $this->new_asset_value,
            'difference_amount' => $this->difference_amount,

            'difference_account_id' => $this->difference_account_id,
            'difference_account' => $this->whenLoaded('differenceAccount'),

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