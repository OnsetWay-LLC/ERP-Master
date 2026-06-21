<?php

namespace App\Http\Controllers\Api\DiscountApproval;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountApproval\RespondDiscountApprovalRequest;
use App\Http\Resources\DiscountApprovalRequestResource;
use App\Models\DiscountApprovalRequest;
use App\Services\DiscountApproval\DiscountApprovalService;
use Illuminate\Http\JsonResponse;

class DiscountApprovalController extends Controller
{
    public function __construct(
        private readonly DiscountApprovalService $service
    ) {}

    public function index(): JsonResponse
    {
        $requests = $this->service->pendingRequests();

        return response()->json([
            'message' => 'Pending discount approval requests retrieved successfully.',
            'data' => DiscountApprovalRequestResource::collection($requests),
        ]);
    }

    public function respond(
        RespondDiscountApprovalRequest $request,
        DiscountApprovalRequest $discountApprovalRequest
    ): JsonResponse {
        $approval = $this->service->respond(
            $discountApprovalRequest,
            $request->validated()
        );

        return response()->json([
            'message' => 'Discount approval request responded successfully.',
            'data' => new DiscountApprovalRequestResource($approval),
        ]);
    }
}