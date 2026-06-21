<?php

namespace App\Http\Resources\SalesOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'status' => $this->status,

            'customer' => [
                'id' => $this->customer?->id,
                'name_ar' => $this->customer?->name_ar,
                'name_en' => $this->customer?->name_en,
            ],

            'net_total' => $this->net_total,
            'tax_total' => $this->tax_total,
            'fees_total' => $this->fees_total,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'grand_total' => $this->grand_total,

            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'taxes' => SalesOrderTaxResource::collection($this->whenLoaded('taxes')),
            'fees' => SalesOrderFeeResource::collection($this->whenLoaded('fees')),

            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'pick_list' => $this->whenLoaded('pickList', function () {
    return [
        'id' => $this->pickList?->id,
        'pick_list_number' => $this->pickList?->pick_list_number,
        'status' => $this->pickList?->status,
    ];
}),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}