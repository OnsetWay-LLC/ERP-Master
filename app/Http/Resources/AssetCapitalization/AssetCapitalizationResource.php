<?php

namespace App\Http\Resources\AssetCapitalization;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetCapitalizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'finance_book' => $this->targetAsset?->assetCategory?->finance_book,

            'target_asset_id' => $this->target_asset_id,
            'target_asset_name_ar' => $this->targetAsset?->asset_name_ar,
            'target_asset_name_en' => $this->targetAsset?->asset_name_en,
            'target_asset' => $this->whenLoaded('targetAsset'),

            'posting_date' => $this->posting_date?->format('Y-m-d'),
            'posting_time' => $this->posting_time,

            'consumed_asset_total_value' => $this->consumed_asset_total_value,
            'status' => $this->status,

            'items' => $this->whenLoaded('items'),

            'created_by' => $this->created_by,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'submitted_by' => $this->submitted_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}