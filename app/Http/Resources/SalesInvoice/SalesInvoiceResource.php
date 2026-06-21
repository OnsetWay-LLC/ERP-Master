<?php

namespace App\Http\Resources\SalesInvoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'sales_order_id' => $this->sales_order_id,
            'delivery_note_id' => $this->delivery_note_id,
            'sales_order' => $this->whenLoaded('salesOrder'),
            'delivery_note' => $this->whenLoaded('deliveryNote'),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer'),
            'sales_person_id' => $this->sales_person_id,
            'sales_person' => $this->whenLoaded('salesPerson'),
            'posting_date' => $this->posting_date,
            'posting_time' => $this->posting_time,
            'payment_due_date' => $this->payment_due_date,
            'posting_method' => $this->posting_method,
            'payment_mode' => $this->payment_mode,
            'payment_account_id' => $this->payment_account_id,
            'receivable_account_id' => $this->receivable_account_id,
            'sales_account_id' => $this->sales_account_id,
            'cogs_account_id' => $this->cogs_account_id,
            'stock_account_id' => $this->stock_account_id,
            'net_total' => (float) $this->net_total,
            'tax_total' => (float) $this->tax_total,
            'fees_total' => (float) $this->fees_total,
            'discount_percentage' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'grand_total' => (float) $this->grand_total,
            'paid_amount' => (float) $this->paid_amount,
            'credit_note_amount' => (float) $this->credit_note_amount,
            'outstanding_amount' => (float) $this->outstanding_amount,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'pending_discount_approval' => $this->whenLoaded('pendingDiscountApproval', function () {
                return $this->pendingDiscountApproval ? [
                    'id' => $this->pendingDiscountApproval->id,
                    'requested_discount_percentage' => (float) $this->pendingDiscountApproval->requested_discount_percentage,
                    'allowed_discount_percentage' => (float) $this->pendingDiscountApproval->allowed_discount_percentage,
                    'status' => $this->pendingDiscountApproval->status,
                ] : null;
            }),

            'items' => $this->whenLoaded('items'),
            'taxes' => $this->whenLoaded('taxes'),
            'fees' => $this->whenLoaded('fees'),
        ];
    }
}