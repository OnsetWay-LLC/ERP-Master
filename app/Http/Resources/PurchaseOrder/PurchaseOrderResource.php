<?php

namespace App\Http\Resources\PurchaseOrder;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'series' => $this->series,
            'supplier_id' => $this->supplier_id,
            'posting_date' => $this->posting_date,
            'required_by_date' => $this->required_by_date,
             
            'total_quantity' => (float) $this->total_quantity,
            'net_total' => (float) $this->net_total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'discount_percentage' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'grand_total' => (float) $this->grand_total,

            'status' => $this->status,

            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),
        ];
    }
}