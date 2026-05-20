<?php

namespace App\Http\Controllers\Api\BankReconciliation;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankReconciliation\BankReconciliationRequest;
use App\Services\BankReconciliation\BankReconciliationService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class BankReconciliationController extends Controller
{
    public function __construct(
        private readonly BankReconciliationService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function calculate(BankReconciliationRequest $request): JsonResponse
    {
        try {
            $result = $this->service->calculate(
                $request->validated(),
                $this->companyId()
            );

            return response()->json([
                'status' => true,
                'data' => $result,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}