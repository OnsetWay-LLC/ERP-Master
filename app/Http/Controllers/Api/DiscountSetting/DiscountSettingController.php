<?php

namespace App\Http\Controllers\Api\DiscountSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountSetting\StoreDiscountSettingRequest;
use App\Http\Requests\DiscountSetting\UpdateDiscountSettingRequest;
use App\Http\Resources\DiscountSetting\DiscountSettingResource;
use App\Services\DiscountSetting\DiscountSettingService;
use Illuminate\Http\JsonResponse;
use App\Models\DiscountSetting;
class DiscountSettingController extends Controller
{
    public function __construct(
        private readonly DiscountSettingService $service
    ) {}
public function store(
    StoreDiscountSettingRequest $request
): JsonResponse
{
    $setting = $this->service->store(
        $request->validated()
    );

    return response()->json([
        'message' => 'Discount settings saved successfully.',
        'data' => new DiscountSettingResource($setting),
    ]);
}
    public function show()
{
    $setting = $this->service->show();

    if (! $setting) {
        return response()->json([
            'status' => false,
            'message' => 'No discount settings found.'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $setting
    ]);
}

    public function update(UpdateDiscountSettingRequest $request): JsonResponse
    {
        $setting = $this->service->update($request->validated());

        return response()->json([
            'message' => 'Discount settings updated successfully.',
            'data' => new DiscountSettingResource($setting),
        ]);
    }
    public function destroy(DiscountSetting $discountSetting): JsonResponse
{
    $this->service->delete($discountSetting);

    return response()->json([
        'message' => 'Discount setting deleted successfully.',
    ]);
}
}