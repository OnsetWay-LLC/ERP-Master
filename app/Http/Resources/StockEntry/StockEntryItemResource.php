<?php

namespace App\Http\Resources\StockEntry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockEntryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_entry_id' => $this->stock_entry_id,

            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item'),

            'barcode' => $this->barcode,

            'source_warehouse_id' => $this->source_warehouse_id,
            'source_warehouse' => $this->whenLoaded('sourceWarehouse'),

            'target_warehouse_id' => $this->target_warehouse_id,
            'target_warehouse' => $this->whenLoaded('targetWarehouse'),

            'quantity' => $this->quantity,
            'basic_rate' => $this->basic_rate,

            'incoming_value' => $this->incoming_value,
            'outgoing_value' => $this->outgoing_value,
            'value_difference' => $this->value_difference,
        ];
    }
}