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
    $this->approvalRequest->loadMissing([
        'invoice',
        'salesOrder',
        'requester',
    ]);

    $documentType = $this->approvalRequest->sales_order_id
        ? 'sales_order'
        : 'sales_invoice';

    $requesterName = $this->approvalRequest->requester?->name ?? 'Unknown User';

    return [
        'type' => 'discount_approval_requested',

        'title' => 'Discount Approval Required',

        'message' => "{$requesterName} requested approval for a discount.",

        'approval_request_id' => $this->approvalRequest->id,

        'document_type' => $documentType,
        'sales_order_id' => $this->approvalRequest->sales_order_id,
        'sales_invoice_id' => $this->approvalRequest->sales_invoice_id,

        'document_number' => $this->approvalRequest->sales_order_id
            ? $this->approvalRequest->salesOrder?->order_number
            : $this->approvalRequest->invoice?->invoice_number,

        'requested_by_id' => $this->approvalRequest->requested_by,
        'requested_by_name' => $requesterName,

        'requested_discount_percentage' => (float) $this->approvalRequest->requested_discount_percentage,
        'allowed_discount_percentage' => (float) $this->approvalRequest->allowed_discount_percentage,

        'approval_level' => $this->approvalRequest->approval_level,
        'status' => $this->approvalRequest->status,

        'created_at' => $this->approvalRequest->created_at?->toDateTimeString(),
    ];
}
}
