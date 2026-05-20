<?php

namespace App\Http\Resources\Asset;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'asset_item_id' => $this->asset_item_id,
            'asset_item' => $this->whenLoaded('assetItem'),

            'asset_category_id' => $this->asset_category_id,
            'asset_category' => $this->whenLoaded('assetCategory'),

            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location'),

            'asset_name_ar' => $this->asset_name_ar,
            'asset_name_en' => $this->asset_name_en,
            'asset_type' => $this->asset_type,

            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'net_purchase_amount' => $this->net_purchase_amount,
            'available_for_use_date' => $this->available_for_use_date?->format('Y-m-d'),
            'asset_quantity' => $this->asset_quantity,
            'salvage_value' => $this->salvage_value,

            'purchase_receipt_id' => $this->purchase_receipt_id,
            'status' => $this->status,
            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}