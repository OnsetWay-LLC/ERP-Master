<?php

namespace App\Http\Resources\StockEntry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->item_id,

            'item_code' => $this->item?->item_code,

            'item_name_ar' => $this->item?->item_name_ar,

            'item_name_en' => $this->item?->item_name_en,

            'warehouse_id' => $this->warehouse_id,

            'warehouse_name_ar' => $this->warehouse?->name_ar,

            'warehouse_name_en' => $this->warehouse?->name_en,

            'warehouse_type' => $this->warehouse?->is_group
                ? 'parent'
                : 'child',

            'quantity' => $this->quantity,

            'reserved_quantity' => $this->reserved_quantity,

            'available_quantity' =>
                $this->quantity - $this->reserved_quantity,

            'average_rate' => $this->average_rate,

            'stock_value' => $this->stock_value,
        ];
    }
}