<?php

namespace App\Http\Controllers\Api\AssetScrapping;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetScrapping\StoreAssetScrappingRequest;
use App\Http\Requests\AssetScrapping\UpdateAssetScrappingRequest;
use App\Http\Resources\AssetScrapping\AssetScrappingResource;
use App\Models\AssetScrapping;
use App\Services\AssetScrapping\AssetScrappingService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class AssetScrappingController extends Controller
{
    public function __construct(
        private readonly AssetScrappingService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => AssetScrappingResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreAssetScrappingRequest $request): JsonResponse
    {
        try {
            $scrapping = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset scrapping created successfully.',
                'data' => new AssetScrappingResource($scrapping),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(AssetScrapping $assetScrapping): JsonResponse
    {
        if ((int) $assetScrapping->company_id !== $this->companyId()) {
            abort(404);
        }

        return response()->json([
            'status' => true,
            'data' => new AssetScrappingResource(
                $assetScrapping->load([
                    'asset.assetItem',
                    'asset.assetCategory',
                    'asset.location',
                    'assetCategory',
                    'fixedAssetAccount',
                    'accumulatedDepreciationAccount',
                    'lossOnDisposalAccount',
                    'journalEntry.lines.account',
                ])
            ),
        ]);
    }

    public function update(
        UpdateAssetScrappingRequest $request,
        AssetScrapping $assetScrapping
    ): JsonResponse {
        if ((int) $assetScrapping->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update(
                $assetScrapping,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset scrapping updated successfully.',
                'data' => new AssetScrappingResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(AssetScrapping $assetScrapping): JsonResponse
    {
        if ((int) $assetScrapping->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $submitted = $this->service->submit(
                $assetScrapping,
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset scrapping submitted successfully.',
                'data' => new AssetScrappingResource($submitted),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AssetScrapping $assetScrapping): JsonResponse
    {
        if ((int) $assetScrapping->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($assetScrapping);

            return response()->json([
                'status' => true,
                'message' => 'Asset scrapping deleted successfully.',
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
}