<?php

namespace App\Services\DiscountApproval;

use App\Models\DiscountApprovalRequest;
use App\Notifications\DiscountApprovalRespondedNotification;
use App\Services\SalesInvoice\SalesInvoiceService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DiscountApprovalService
{
    public function __construct(
        private readonly SalesInvoiceService $salesInvoiceService
    ) {}

    public function pendingRequests()
    {
        return DiscountApprovalRequest::query()
            ->with(['invoice', 'requester', 'approver'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function respond(
        DiscountApprovalRequest $approvalRequest,
        array $data
    ): DiscountApprovalRequest {
        if ($approvalRequest->status !== 'pending') {
            throw new RuntimeException('This discount approval request has already been responded.');
        }

        return DB::transaction(function () use ($approvalRequest, $data) {
            $approvalRequest->load(['invoice', 'requester']);

            if ($data['status'] === 'approved') {
                $this->salesInvoiceService->applyApprovedDiscount(
                    $approvalRequest->invoice,
                    (float) $approvalRequest->requested_discount_percentage
                );
            }

            $approvalRequest->update([
                'status' => $data['status'],
                'approved_by' => auth('api')->id(),
                'rejection_reason' => $data['status'] === 'rejected'
                    ? ($data['rejection_reason'] ?? null)
                    : null,
                'responded_at' => now(),
            ]);

            $approvalRequest->requester?->notify(
                new DiscountApprovalRespondedNotification($approvalRequest->fresh(['invoice', 'requester', 'approver']))
            );

            return $approvalRequest->fresh(['invoice', 'requester', 'approver']);
        });
    }
}