<?php

namespace App\Notifications;

use App\Models\DiscountApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscountApprovalRespondedNotification extends Notification
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
        $approved = $this->approvalRequest->status === 'approved';

        return [
            'type' => 'discount_approval_responded',
            'title' => $approved ? 'Discount approved' : 'Discount rejected',
            'message' => $approved
                ? 'Your discount request has been approved.'
                : 'Your discount request has been rejected.',
            'approval_request_id' => $this->approvalRequest->id,
            'sales_invoice_id' => $this->approvalRequest->sales_invoice_id,
            'invoice_number' => $this->approvalRequest->invoice?->invoice_number,
            'requested_discount_percentage' => (float) $this->approvalRequest->requested_discount_percentage,
            'status' => $this->approvalRequest->status,
            'rejection_reason' => $this->approvalRequest->rejection_reason,
        ];
    }
}