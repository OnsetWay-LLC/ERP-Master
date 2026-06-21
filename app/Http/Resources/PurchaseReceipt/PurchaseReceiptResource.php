<?php

namespace App\Http\Resources\PurchaseReceipt;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReceiptResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,

            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order' => $this->whenLoaded('purchaseOrder'),

            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier'),

            'receipt_date' => $this->receipt_date,
            'posting_time' => $this->posting_time,

            'total_qty' => (float) $this->total_qty,
            'total' => (float) $this->total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'additional_discount_percentage' => (float) $this->additional_discount_percentage,
            'additional_discount_amount' => (float) $this->additional_discount_amount,
            'grand_total' => (float) $this->grand_total,

            'status' => $this->status,

            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}