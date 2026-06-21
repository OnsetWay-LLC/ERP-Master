<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountApprovalRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'invoice' => [
                'id' => $this->invoice?->id,
                'invoice_number' => $this->invoice?->invoice_number,
                'grand_total' => (float) $this->invoice?->grand_total,
            ],

            'requested_by' => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->name,
                'email' => $this->requester?->email,
            ],

            'approved_by' => $this->when($this->approver, [
                'id' => $this->approver?->id,
                'name' => $this->approver?->name,
                'email' => $this->approver?->email,
            ]),

            'requested_discount_percentage' => (float) $this->requested_discount_percentage,
            'allowed_discount_percentage' => (float) $this->allowed_discount_percentage,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'responded_at' => $this->responded_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}