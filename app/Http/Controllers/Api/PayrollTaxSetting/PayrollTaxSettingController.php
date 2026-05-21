<?php

namespace App\Http\Controllers\Api\PayrollTaxSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollTaxSetting\StorePayrollTaxSettingRequest;
use App\Http\Resources\PayrollTaxSetting\PayrollTaxSettingResource;
use App\Services\PayrollTaxSetting\PayrollTaxSettingService;
use Illuminate\Http\JsonResponse;

class PayrollTaxSettingController extends Controller
{
    public function __construct(
        private readonly PayrollTaxSettingService $service
    ) {}

    public function show(): JsonResponse
    {
        $setting = $this->service->getActive();

        return response()->json([
            'message' => 'Payroll tax setting retrieved successfully.',
            'data' => $setting ? new PayrollTaxSettingResource($setting) : null,
        ]);
    }

    public function storeOrUpdate(StorePayrollTaxSettingRequest $request): JsonResponse
    {
        $setting = $this->service->storeOrUpdate($request->validated());

        return response()->json([
            'message' => 'Payroll tax setting saved successfully.',
            'data' => new PayrollTaxSettingResource($setting),
        ]);
    }
}