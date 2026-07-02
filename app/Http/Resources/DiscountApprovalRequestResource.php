<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountApprovalRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $documentType = $this->sales_order_id
            ? 'sales_order'
            : 'sales_invoice';

        $documentNumber = $this->sales_order_id
            ? $this->salesOrder?->order_number
            : $this->invoice?->invoice_number;

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'document_type' => $documentType,
            'document_number' => $documentNumber,

            'sales_order_id' => $this->sales_order_id,
            'sales_invoice_id' => $this->sales_invoice_id,

            'sales_order' => $this->whenLoaded('salesOrder'),
            'sales_invoice' => $this->whenLoaded('invoice'),

            'requested_by' => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->name,
            ],

            'approved_by' => [
                'id' => $this->approver?->id,
                'name' => $this->approver?->name,
            ],

            'forwarded_by' => [
                'id' => $this->forwarder?->id,
                'name' => $this->forwarder?->name,
            ],

            'requested_discount_percentage' => (float) $this->requested_discount_percentage,
            'allowed_discount_percentage' => (float) $this->allowed_discount_percentage,

            'approval_level' => $this->approval_level,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,

            'responded_at' => $this->responded_at?->toDateTimeString(),
            'forwarded_at' => $this->forwarded_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}