<?php

namespace App\Http\Resources\DeliveryNote;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_note_number' => $this->delivery_note_number,
            'posting_date' => $this->posting_date,
            'posting_time' => $this->posting_time,
            'status' => $this->status,

            'sales_order' => [
                'id' => $this->salesOrder?->id,
                'order_number' => $this->salesOrder?->order_number,
                'status' => $this->salesOrder?->status,
            ],

            'pick_list' => [
                'id' => $this->pickList?->id,
                'pick_list_number' => $this->pickList?->pick_list_number,
                'status' => $this->pickList?->status,
            ],

            'customer' => [
                'id' => $this->customer?->id,
                'name_ar' => $this->customer?->name_ar,
                'name_en' => $this->customer?->name_en,
            ],

            'total_qty' => $this->total_qty,
            'net_total' => $this->net_total,
            'tax_total' => $this->tax_total,
            'fees_total' => $this->fees_total,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'grand_total' => $this->grand_total,

            'items' => DeliveryNoteItemResource::collection($this->whenLoaded('items')),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}