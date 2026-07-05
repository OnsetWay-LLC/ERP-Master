<?php

namespace App\Services\DiscountApproval;

use App\Models\DiscountApprovalRequest;
use App\Models\User;
use App\Notifications\DiscountApprovalRequestedNotification;
use App\Notifications\DiscountApprovalRespondedNotification;
use App\Services\SalesInvoice\SalesInvoiceService;
use App\Services\SalesOrder\SalesOrderService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DiscountApprovalService
{
    public function __construct(
        private readonly SalesInvoiceService $salesInvoiceService,
        private readonly SalesOrderService $salesOrderService
    ) {}

    public function pendingRequests()
    {
        $user = auth('api')->user();

        return DiscountApprovalRequest::query()
            ->with([
                'salesOrder',
                'invoice',
                'requester',
                'approver',
                'forwarder',
            ])
            ->when($user->hasRole('Accountant Chief'), function ($q) {
                $q->where('status', 'pending_department_manager_approval');
            })
            ->when($user->hasRole('CFO'), function ($q) {
                $q->where('status', 'pending_cfo_approval');
            })
            ->latest()
            ->get();
    }

    public function myRequests()
    {
        return DiscountApprovalRequest::query()
            ->with([
                'salesOrder',
                'invoice',
                'requester',
                'approver',
                'forwarder',
            ])
            ->where('requested_by', auth('api')->id())
            ->latest()
            ->get();
    }

    public function respond(
        DiscountApprovalRequest $approvalRequest,
        array $data
    ): DiscountApprovalRequest {
        return DB::transaction(function () use ($approvalRequest, $data) {
            $approvalRequest->load([
                'salesOrder',
                'invoice',
                'requester',
            ]);

            if (in_array($approvalRequest->status, ['approved', 'rejected'], true)) {
                throw new RuntimeException('This discount approval request has already been completed.');
            }

            return match ($data['action']) {
                'approve' => $this->approve($approvalRequest),
                'reject' => $this->reject($approvalRequest, $data['rejection_reason'] ?? null),
                'forward_to_cfo' => $this->forwardToCfo($approvalRequest),
                default => throw new RuntimeException('Invalid approval action.'),
            };
        });
    }

    private function approve(DiscountApprovalRequest $approvalRequest): DiscountApprovalRequest
    {
        $user = auth('api')->user();

        if ($approvalRequest->status === 'pending_department_manager_approval') {
            if (! $user->hasRole('Accountant Chief')) {
                throw new RuntimeException('Only Accountant Chief can approve this request.');
            }

            if ($approvalRequest->approval_level === 'cfo') {
                throw new RuntimeException('This request exceeds your approval limit. Please forward it to CFO.');
            }
        } elseif ($approvalRequest->status === 'pending_cfo_approval') {
            if (! $user->hasRole('CFO')) {
                throw new RuntimeException('Only CFO can approve this request.');
            }
        } else {
            throw new RuntimeException('This request cannot be approved.');
        }

        $this->applyDiscountToLinkedDocument($approvalRequest);

        $approvalRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'rejection_reason' => null,
            'responded_at' => now(),
        ]);

        $approvalRequest->requester?->notify(
            new DiscountApprovalRespondedNotification(
                $approvalRequest->fresh([
                    'salesOrder',
                    'invoice',
                    'requester',
                    'approver',
                    'forwarder',
                ])
            )
        );

        return $approvalRequest->fresh([
            'salesOrder',
            'invoice',
            'requester',
            'approver',
            'forwarder',
        ]);
    }

    private function reject(
        DiscountApprovalRequest $approvalRequest,
        ?string $reason
    ): DiscountApprovalRequest {
        $user = auth('api')->user();

        if (! $reason) {
            throw new RuntimeException('Rejection reason is required.');
        }

        if ($approvalRequest->status === 'pending_department_manager_approval') {
            if (! $user->hasRole('Accountant Chief')) {
                throw new RuntimeException('Only Accountant Chief can reject this request.');
            }
        } elseif ($approvalRequest->status === 'pending_cfo_approval') {
            if (! $user->hasRole('CFO')) {
                throw new RuntimeException('Only CFO can reject this request.');
            }
        } else {
            throw new RuntimeException('This request cannot be rejected.');
        }

        $approvalRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'rejection_reason' => $reason,
            'responded_at' => now(),
        ]);

        $approvalRequest->requester?->notify(
            new DiscountApprovalRespondedNotification(
                $approvalRequest->fresh([
                    'salesOrder',
                    'invoice',
                    'requester',
                    'approver',
                    'forwarder',
                ])
            )
        );

        return $approvalRequest->fresh([
            'salesOrder',
            'invoice',
            'requester',
            'approver',
            'forwarder',
        ]);
    }

    private function forwardToCfo(
        DiscountApprovalRequest $approvalRequest
    ): DiscountApprovalRequest {
        $user = auth('api')->user();

        if (! $user->hasRole('Accountant Chief')) {
            throw new RuntimeException('Only Accountant Chief can forward this request to CFO.');
        }

        if ($approvalRequest->status !== 'pending_department_manager_approval') {
            throw new RuntimeException('This request cannot be forwarded to CFO.');
        }

        if ($approvalRequest->approval_level !== 'cfo') {
            throw new RuntimeException('This request does not require CFO approval.');
        }

        $approvalRequest->update([
            'status' => 'pending_cfo_approval',
            'forwarded_by' => $user->id,
            'forwarded_at' => now(),
        ]);

        $freshApprovalRequest = $approvalRequest->fresh([
            'salesOrder',
            'invoice',
            'requester',
            'forwarder',
        ]);

        foreach (User::role('CFO')->get() as $cfo) {
            $cfo->notify(
                new DiscountApprovalRequestedNotification($freshApprovalRequest)
            );
        }

        return $approvalRequest->fresh([
            'salesOrder',
            'invoice',
            'requester',
            'approver',
            'forwarder',
        ]);
    }

    private function applyDiscountToLinkedDocument(
        DiscountApprovalRequest $approvalRequest
    ): void {
        $discountPercentage = (float) $approvalRequest->requested_discount_percentage;

        if ($approvalRequest->sales_order_id) {
            if (! $approvalRequest->salesOrder) {
                throw new RuntimeException('Linked sales order was not found.');
            }

            $this->salesOrderService->applyApprovedDiscount(
                $approvalRequest->salesOrder,
                $discountPercentage
            );

            return;
        }

        if ($approvalRequest->sales_invoice_id) {
            if (! $approvalRequest->invoice) {
                throw new RuntimeException('Linked sales invoice was not found.');
            }

            $this->salesInvoiceService->applyApprovedDiscount(
                $approvalRequest->invoice,
                $discountPercentage
            );

            return;
        }

        throw new RuntimeException('Approval request is not linked to a valid document.');
    }
}