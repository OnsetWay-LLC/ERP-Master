<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->data['type'] ?? null,
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,

            'approval_request_id' => $this->data['approval_request_id'] ?? null,
            'sales_invoice_id' => $this->data['sales_invoice_id'] ?? null,
            'invoice_number' => $this->data['invoice_number'] ?? null,

            'requested_discount_percentage' => $this->data['requested_discount_percentage'] ?? null,
            'status' => $this->data['status'] ?? null,
            'rejection_reason' => $this->data['rejection_reason'] ?? null,

            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}