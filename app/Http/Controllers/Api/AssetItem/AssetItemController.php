<?php

namespace App\Http\Controllers\Api\AssetItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetItem\StoreAssetItemRequest;
use App\Http\Requests\AssetItem\UpdateAssetItemRequest;
use App\Http\Resources\AssetItem\AssetItemResource;
use App\Models\AssetItem;
use App\Services\AssetItem\AssetItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AssetItemController extends Controller
{
    public function __construct(
        private readonly AssetItemService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->getAll(
            $this->companyId(),
            $request->only(['asset_category_id', 'is_active'])
        );

        return response()->json([
            'status' => true,
            'data' => AssetItemResource::collection($items),
        ]);
    }

    public function store(StoreAssetItemRequest $request): JsonResponse
    {
        try {
            $item = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset item created successfully.',
                'data' => new AssetItemResource($item),
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
        $item = AssetItem::query()
            ->where('company_id', $this->companyId())
            ->with('assetCategory')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new AssetItemResource($item),
        ]);
    }

    public function update(UpdateAssetItemRequest $request, int $id): JsonResponse
    {
        $item = AssetItem::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update($item, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Asset item updated successfully.',
                'data' => new AssetItemResource($updated),
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
        $item = AssetItem::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $this->service->delete($item);

        return response()->json([
            'status' => true,
            'message' => 'Asset item deleted successfully.',
        ]);
    }
}