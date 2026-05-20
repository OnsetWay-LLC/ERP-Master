<?php

namespace App\Http\Controllers\Api\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use App\Services\Asset\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AssetController extends Controller
{
    public function __construct(
        private readonly AssetService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $assets = $this->service->getAll(
            $this->companyId(),
            $request->only([
                'asset_item_id',
                'asset_category_id',
                'location_id',
                'asset_type',
                'status',
            ])
        );

        return response()->json([
            'status' => true,
            'data' => AssetResource::collection($assets),
        ]);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        try {
            $asset = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset created successfully.',
                'data' => new AssetResource($asset),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $asset = Asset::query()
            ->where('company_id', $this->companyId())
            ->with(['assetItem', 'assetCategory', 'location'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new AssetResource($asset),
        ]);
    }

    public function update(UpdateAssetRequest $request, int $id): JsonResponse
    {
        $asset = Asset::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update($asset, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Asset updated successfully.',
                'data' => new AssetResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $asset = Asset::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $this->service->delete($asset);

            return response()->json([
                'status' => true,
                'message' => 'Asset deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}