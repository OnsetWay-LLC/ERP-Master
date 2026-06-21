<?php

namespace App\Http\Resources\PickList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pick_list_number' => $this->pick_list_number,
            'posting_date' => $this->posting_date,
            'status' => $this->status,

            'sales_order' => [
                'id' => $this->salesOrder?->id,
                'order_number' => $this->salesOrder?->order_number,
                'status' => $this->salesOrder?->status,
            ],

            'customer' => [
                'id' => $this->customer?->id,
                'name_ar' => $this->customer?->name_ar,
                'name_en' => $this->customer?->name_en,
            ],

            'items' => PickListItemResource::collection($this->whenLoaded('items')),

            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}