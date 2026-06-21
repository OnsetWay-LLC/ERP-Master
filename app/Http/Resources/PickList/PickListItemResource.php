<?php

namespace App\Http\Resources\PickList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'sales_order_item_id' => $this->sales_order_item_id,

            'item' => [
                'id' => $this->item_id,
                'code' => $this->item_code,
                'name_ar' => $this->item_name_ar,
                'name_en' => $this->item_name_en,
            ],

            'warehouse' => [
                'id' => $this->warehouse?->id,
                'name_ar' => $this->warehouse?->name_ar,
                'name_en' => $this->warehouse?->name_en,
            ],

            'required_quantity' => $this->required_quantity,
            'picked_quantity' => $this->picked_quantity,
        ];
    }
}