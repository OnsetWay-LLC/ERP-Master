<?php

namespace App\Http\Resources\SalesOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

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

            'available_stock' => $this->available_stock,
            'quantity' => $this->quantity,
            'rate' => $this->rate,
            'amount' => $this->amount,
        ];
    }
}