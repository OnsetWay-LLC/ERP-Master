<?php

namespace App\Http\Controllers\Api\AssetCapitalization;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCapitalization\StoreAssetCapitalizationRequest;
use App\Http\Requests\AssetCapitalization\UpdateAssetCapitalizationRequest;
use App\Http\Resources\AssetCapitalization\AssetCapitalizationResource;
use App\Models\AssetCapitalization;
use App\Services\AssetCapitalization\AssetCapitalizationService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class AssetCapitalizationController extends Controller
{
    public function __construct(
        private readonly AssetCapitalizationService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => AssetCapitalizationResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreAssetCapitalizationRequest $request): JsonResponse
    {
        try {
            $capitalization = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset capitalization created successfully.',
                'data' => new AssetCapitalizationResource($capitalization),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

   public function show(AssetCapitalization $assetCapitalization): JsonResponse
{
    if ((int) $assetCapitalization->company_id !== $this->companyId()) {
        abort(404);
    }

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $assetCapitalization->id,
            'company_id' => $assetCapitalization->company_id,
            'series' => $assetCapitalization->series,
            'target_asset_id' => $assetCapitalization->target_asset_id,
            'posting_date' => $assetCapitalization->posting_date,
            'posting_time' => $assetCapitalization->posting_time,
            'consumed_asset_total_value' => (float) $assetCapitalization->consumed_asset_total_value,
            'status' => $assetCapitalization->status,
            'submitted_at' => $assetCapitalization->submitted_at,
            'submitted_by' => $assetCapitalization->submitted_by,
            'created_by' => $assetCapitalization->created_by,
            'created_at' => $assetCapitalization->created_at,
            'updated_at' => $assetCapitalization->updated_at,
        ],
    ]);
}
    public function update(
        UpdateAssetCapitalizationRequest $request,
        AssetCapitalization $assetCapitalization
    ): JsonResponse {
        if ((int) $assetCapitalization->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update(
                $assetCapitalization,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset capitalization updated successfully.',
                'data' => new AssetCapitalizationResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(AssetCapitalization $assetCapitalization): JsonResponse
    {
        if ((int) $assetCapitalization->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $submitted = $this->service->submit(
                $assetCapitalization,
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset capitalization submitted successfully.',
                'data' => new AssetCapitalizationResource($submitted),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AssetCapitalization $assetCapitalization): JsonResponse
    {
        if ((int) $assetCapitalization->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($assetCapitalization);

            return response()->json([
                'status' => true,
                'message' => 'Asset capitalization deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function targetAssets(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availableTargetAssets($this->companyId()),
        ]);
    }

    public function consumedAssets(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availableConsumedAssets(
                $this->companyId(),
                request('target_asset_id')
            ),
        ]);
    }
}