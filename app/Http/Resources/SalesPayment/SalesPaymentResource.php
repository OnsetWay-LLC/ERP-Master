<?php

namespace App\Http\Resources\SalesPayment;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,

            'sales_invoice_id' => $this->sales_invoice_id,
            'invoice_number' => $this->salesInvoice?->invoice_number,

            'customer_id' => $this->customer_id,
            'customer' => $this->customer?->name_ar ?? $this->customer?->name_en,

            'payment_date' => $this->payment_date,
            'payment_time' => $this->payment_time,

            'posting_method' => $this->posting_method,
            'payment_mode' => $this->payment_mode,

            'receivable_account_id' => $this->receivable_account_id,
            'payment_account_id' => $this->payment_account_id,

            'invoice_amount' => (float) $this->invoice_amount,
            'paid_amount' => (float) $this->paid_amount,
            'outstanding_before' => (float) $this->outstanding_before,
            'outstanding_after' => (float) $this->outstanding_after,

            'status' => $this->status,
            'journal_entry_id' => $this->journal_entry_id,

            'created_at' => $this->created_at,
        ];
    }
}