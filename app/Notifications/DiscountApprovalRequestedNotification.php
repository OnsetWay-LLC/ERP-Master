<?php

namespace App\Notifications;

use App\Models\DiscountApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscountApprovalRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly DiscountApprovalRequest $approvalRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'discount_approval_requested',
            'title' => 'Discount approval required',
            'message' => 'A discount approval request has been submitted.',
            'approval_request_id' => $this->approvalRequest->id,
            'sales_invoice_id' => $this->approvalRequest->sales_invoice_id,
            'invoice_number' => $this->approvalRequest->invoice?->invoice_number,
            'requested_by' => $this->approvalRequest->requester?->name,
            'requested_discount_percentage' => (float) $this->approvalRequest->requested_discount_percentage,
            'allowed_discount_percentage' => (float) $this->approvalRequest->allowed_discount_percentage,
            'status' => $this->approvalRequest->status,
        ];
    }
}