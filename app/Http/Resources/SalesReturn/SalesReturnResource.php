<?php

namespace App\Http\Resources\SalesReturn;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'return_number' => $this->return_number,

            'sales_invoice_id' => $this->sales_invoice_id,
            'invoice_number' => $this->salesInvoice?->invoice_number,

            'customer_id' => $this->customer_id,
            'customer' => $this->customer?->name_ar ?? $this->customer?->name_en,

            'posting_date' => $this->posting_date,
            'posting_time' => $this->posting_time,
            'payment_due_date' => $this->payment_due_date,
            'return_reason' => $this->return_reason,

            'posting_method' => $this->posting_method,
            'sales_account_id' => $this->sales_account_id,
            'customer_account_id' => $this->customer_account_id,

            'net_total' => (float) $this->net_total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'discount_apply_on' => $this->discount_apply_on,
            'discount_percentage' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'grand_total' => (float) $this->grand_total,
            'outstanding_amount' => (float) $this->outstanding_amount,

            'status' => $this->status,
            'journal_entry_id' => $this->journal_entry_id,

            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),
        ];
    }
}