<?php

namespace App\Http\Resources\StockEntry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,
            'entry_type' => $this->entry_type,

            'posting_date' => $this->posting_date?->format('Y-m-d'),
            'posting_time' => $this->posting_time,

            'total_incoming_value' => $this->total_incoming_value,
            'total_outgoing_value' => $this->total_outgoing_value,
            'value_difference' => $this->value_difference,

            'status' => $this->status,
            'created_by' => $this->created_by,

            'items' => StockEntryItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}