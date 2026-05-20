<?php

namespace App\Http\Controllers\Api\AssetCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategory\StoreAssetCategoryRequest;
use App\Http\Requests\AssetCategory\UpdateAssetCategoryRequest;
use App\Http\Resources\AssetCategory\AssetCategoryResource;
use App\Models\AssetCategory;
use App\Services\AssetCategory\AssetCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AssetCategoryController extends Controller
{
    public function __construct(
        private readonly AssetCategoryService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->service->getAll(
            $this->companyId(),
            $request->only(['is_active'])
        );

        return response()->json([
            'status' => true,
            'data' => AssetCategoryResource::collection($categories),
        ]);
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset category created successfully.',
                'data' => new AssetCategoryResource($category),
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
        $category = AssetCategory::query()
            ->where('company_id', $this->companyId())
            ->with([
                'fixedAssetAccount',
                'accumulatedDepreciationAccount',
                'depreciationExpenseAccount',
            ])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new AssetCategoryResource($category),
        ]);
    }

    public function update(UpdateAssetCategoryRequest $request, int $id): JsonResponse
    {
        $category = AssetCategory::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update($category, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Asset category updated successfully.',
                'data' => new AssetCategoryResource($updated),
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
        $category = AssetCategory::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $this->service->delete($category);

            return response()->json([
                'status' => true,
                'message' => 'Asset category deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}