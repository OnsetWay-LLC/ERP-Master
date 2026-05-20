<?php

namespace App\Http\Resources\AssetItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'item_code' => $this->item_code,
            'item_name' => $this->item_name,

            'asset_category_id' => $this->asset_category_id,
            'asset_category' => $this->whenLoaded('assetCategory'),

            'is_active' => $this->is_active,
            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}