<?php

namespace App\Http\Controllers\Api\ItemGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemGroup\IndexItemGroupRequest;
use App\Http\Requests\ItemGroup\StoreItemGroupRequest;
use App\Http\Requests\ItemGroup\UpdateItemGroupRequest;
use App\Http\Resources\ItemGroup\ItemGroupResource;
use App\Models\ItemGroup;
use App\Services\ItemGroup\ItemGroupService;
use Illuminate\Http\JsonResponse;

class ItemGroupController extends Controller
{
    public function __construct(
        protected ItemGroupService $service
    ) {}

    public function index(IndexItemGroupRequest $request): JsonResponse
    {
        $itemGroups = $this->service->getAll($request->validated());

        return response()->json([
            'message' => 'Item groups retrieved successfully.',
            'data' => ItemGroupResource::collection($itemGroups->items()),
            'meta' => [
                'current_page' => $itemGroups->currentPage(),
                'last_page' => $itemGroups->lastPage(),
                'per_page' => $itemGroups->perPage(),
                'total' => $itemGroups->total(),
                'from' => $itemGroups->firstItem(),
                'to' => $itemGroups->lastItem(),
            ],
            'links' => [
                'first' => $itemGroups->url(1),
                'last' => $itemGroups->url($itemGroups->lastPage()),
                'prev' => $itemGroups->previousPageUrl(),
                'next' => $itemGroups->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreItemGroupRequest $request): JsonResponse
    {
        $itemGroup = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Item group created successfully.',
            'data' => new ItemGroupResource($itemGroup),
        ], 201);
    }

    public function show(ItemGroup $itemGroup): JsonResponse
    {
        $itemGroup = $this->service->getById($itemGroup->id);

        return response()->json([
            'message' => 'Item group retrieved successfully.',
            'data' => new ItemGroupResource($itemGroup),
        ]);
    }

    public function update(UpdateItemGroupRequest $request, ItemGroup $itemGroup): JsonResponse
    {
        $itemGroup = $this->service->update($itemGroup, $request->validated());

        return response()->json([
            'message' => 'Item group updated successfully.',
            'data' => new ItemGroupResource($itemGroup),
        ]);
    }

    public function destroy(ItemGroup $itemGroup): JsonResponse
    {
        $this->service->delete($itemGroup);

        return response()->json([
            'message' => 'Item group deleted successfully.',
        ]);
    }
}