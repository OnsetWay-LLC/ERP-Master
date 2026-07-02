<?php

namespace App\Http\Resources\Asset;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Accounting\JournalEntryResource;
class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'asset_item_id' => $this->asset_item_id,
            'asset_item_name_ar' => $this->assetItem?->item_name,
            'asset_item_name_en' => $this->assetItem?->item_name,
            'asset_item' => $this->whenLoaded('assetItem'),

            'asset_category_id' => $this->asset_category_id,
            'asset_category_name_ar' => $this->assetCategory?->name_ar,
            'asset_category_name_en' => $this->assetCategory?->name_en,
            'asset_category' => $this->whenLoaded('assetCategory'),

            'location_id' => $this->location_id,
            'location_name_ar' => $this->location?->name_ar,
            'location_name_en' => $this->location?->name_en,
            'location' => $this->whenLoaded('location'),

           'asset_name_ar' => $this->assetItem?->item_name,
'asset_name_en' => $this->assetItem?->item_name,
'asset_item_name' => $this->assetItem?->item_name,
            'asset_type' => $this->asset_type,

            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'net_purchase_amount' => $this->net_purchase_amount,
            'available_for_use_date' => $this->available_for_use_date?->format('Y-m-d'),
            'asset_quantity' => $this->asset_quantity,
          

            'purchase_invoice_id' => $this->purchase_invoice_id,
            'purchase_receipt_id' => $this->purchase_receipt_id,

            'opening_accumulated_depreciation' => $this->opening_accumulated_depreciation,
            'opening_number_of_booked_depreciations' => $this->opening_number_of_booked_depreciations,
            'journal_entries' => JournalEntryResource::collection(
    $this->whenLoaded('journalEntries')
),
            'status' => $this->status,
            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}