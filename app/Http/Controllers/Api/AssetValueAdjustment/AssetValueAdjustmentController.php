<?php

namespace App\Http\Controllers\Api\AssetValueAdjustment;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetValueAdjustment\StoreAssetValueAdjustmentRequest;
use App\Http\Requests\AssetValueAdjustment\UpdateAssetValueAdjustmentRequest;
use App\Http\Resources\AssetValueAdjustment\AssetValueAdjustmentResource;
use App\Models\AssetValueAdjustment;
use App\Services\AssetValueAdjustment\AssetValueAdjustmentService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class AssetValueAdjustmentController extends Controller
{
    public function __construct(
        private readonly AssetValueAdjustmentService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => AssetValueAdjustmentResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreAssetValueAdjustmentRequest $request): JsonResponse
    {
        try {
            $adjustment = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset value adjustment created successfully.',
                'data' => new AssetValueAdjustmentResource($adjustment),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(AssetValueAdjustment $assetValueAdjustment): JsonResponse
    {
        if ((int) $assetValueAdjustment->company_id !== $this->companyId()) {
            abort(404);
        }

        return response()->json([
            'status' => true,
            'data' => new AssetValueAdjustmentResource(
                $assetValueAdjustment->load([
                    'asset.assetItem',
                    'asset.assetCategory',
                    'asset.location',
                    'assetCategory',
                    'differenceAccount',
                    'journalEntry.lines.account',
                ])
            ),
        ]);
    }

    public function update(
        UpdateAssetValueAdjustmentRequest $request,
        AssetValueAdjustment $assetValueAdjustment
    ): JsonResponse {
        if ((int) $assetValueAdjustment->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update(
                $assetValueAdjustment,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset value adjustment updated successfully.',
                'data' => new AssetValueAdjustmentResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(AssetValueAdjustment $assetValueAdjustment): JsonResponse
    {
        if ((int) $assetValueAdjustment->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $submitted = $this->service->submit(
                $assetValueAdjustment,
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset value adjustment submitted successfully.',
                'data' => new AssetValueAdjustmentResource($submitted),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AssetValueAdjustment $assetValueAdjustment): JsonResponse
    {
        if ((int) $assetValueAdjustment->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($assetValueAdjustment);

            return response()->json([
                'status' => true,
                'message' => 'Asset value adjustment deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function availableAssets(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availableAssets($this->companyId()),
        ]);
    }

    public function differenceAccounts(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->differenceAccounts($this->companyId()),
        ]);
    }
}