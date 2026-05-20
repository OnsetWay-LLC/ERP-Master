<?php

namespace App\Http\Resources\Item;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'item_code' => $this->item_code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'barcode' => $this->barcode,

            'selling_price' => $this->selling_price,
            'purchase_price' => $this->purchase_price,
            'currency_code' => $this->currency_code,
            'status' => $this->status,

            'item_group' => [
                'id' => $this->itemGroup?->id,
                'name_ar' => $this->itemGroup?->name_ar,
                'name_en' => $this->itemGroup?->name_en,
            ],

          
            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],

            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}