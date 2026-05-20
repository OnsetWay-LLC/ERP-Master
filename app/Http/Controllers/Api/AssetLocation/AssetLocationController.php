<?php

namespace App\Http\Controllers\Api\AssetLocation;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetLocation\StoreAssetLocationRequest;
use App\Http\Requests\AssetLocation\UpdateAssetLocationRequest;
use App\Http\Resources\AssetLocation\AssetLocationResource;
use App\Models\AssetLocation;
use App\Services\AssetLocation\AssetLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function __construct(
        private readonly AssetLocationService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $locations = $this->service->getAll(
            $this->companyId(),
            $request->only(['is_active'])
        );

        return response()->json([
            'status' => true,
            'data' => AssetLocationResource::collection($locations),
        ]);
    }

    public function store(StoreAssetLocationRequest $request): JsonResponse
    {
        $location = $this->service->create(
            $request->validated(),
            $this->companyId(),
            auth()->id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Asset location created successfully.',
            'data' => new AssetLocationResource($location),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $location = AssetLocation::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new AssetLocationResource($location),
        ]);
    }

    public function update(UpdateAssetLocationRequest $request, int $id): JsonResponse
    {
        $location = AssetLocation::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $updated = $this->service->update($location, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Asset location updated successfully.',
            'data' => new AssetLocationResource($updated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $location = AssetLocation::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $this->service->delete($location);

        return response()->json([
            'status' => true,
            'message' => 'Asset location deleted successfully.',
        ]);
    }
}