<?php

namespace App\Http\Resources\PurchaseInvoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseInvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'purchase_receipt_id' => $this->purchase_receipt_id,
            'purchase_order_id' => $this->purchase_order_id,
            'supplier_id' => $this->supplier_id,

            'posting_date' => $this->posting_date,
            'posting_time' => $this->posting_time,
            'due_date' => $this->due_date,

            'supplier_invoice_no' => $this->supplier_invoice_no,
            'supplier_invoice_date' => $this->supplier_invoice_date,

            'posting_method' => $this->posting_method,
          
            'net_total' => (float) $this->net_total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'discount_amount' => (float) $this->discount_amount,
            'grand_total' => (float) $this->grand_total,
            'paid_amount' => (float) $this->paid_amount,
            'outstanding_amount' => (float) $this->outstanding_amount,

            'status' => $this->status,
            'journal_entry_id' => $this->journal_entry_id,

            'supplier' => $this->whenLoaded('supplier'),
            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),
        ];
    }
}