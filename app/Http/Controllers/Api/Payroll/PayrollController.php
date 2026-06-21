<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\JsonResponse;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $service
    ) {
    }

    public function generate(
        GeneratePayrollRequest $request
    ): JsonResponse {
        $payroll = $this->service->generate(
            $request->validated()
        );

        return response()->json([
            'message' => 'Payroll generated successfully.',
            'data' => $payroll,
        ]);
    }
}