<?php

namespace App\Http\Controllers\Api\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\IndexItemRequest;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\Item\ItemResource;
use App\Models\Item;
use App\Services\Item\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ItemController extends Controller
{
    public function __construct(
        protected ItemService $service
    ) {}

    public function index(IndexItemRequest $request): JsonResponse
    {
        $items = $this->service->getAll($request->validated());

        return response()->json([
            'message' => 'Items retrieved successfully.',
            'data' => ItemResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
            'links' => [
                'first' => $items->url(1),
                'last' => $items->url($items->lastPage()),
                'prev' => $items->previousPageUrl(),
                'next' => $items->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Item created successfully.',
            'data' => new ItemResource($item),
        ], 201);
    }

    public function show(Item $item): JsonResponse
    {
        $item = $this->service->getById($item->id);

        return response()->json([
            'message' => 'Item retrieved successfully.',
            'data' => new ItemResource($item),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $item = $this->service->update($item, $request->validated());

        return response()->json([
            'message' => 'Item updated successfully.',
            'data' => new ItemResource($item),
        ]);
    }

    public function destroy(Item $item): JsonResponse
    {
        $this->service->delete($item);

        return response()->json([
            'message' => 'Item deleted successfully.',
        ]);
    }
    public function restore(Item $item): JsonResponse
    {
        $this->service->restore($item);

        return response()->json([
            'message' => 'Item restored successfully.',
        ]);
    }
    public function printBarcode(int $id)
{
    return $this->service->printBarcode($id);
}

public function scanBarcode(Request $request): JsonResponse
{
    $request->validate([
        'barcode' => ['required', 'string'],
    ]);

    $item = $this->service->scanBarcode($request->barcode);

    return response()->json([
        'message' => $item ? 'Item found successfully.' : 'Item not found.',
        'found' => (bool) $item,
        'data' => $item ? new ItemResource($item) : null,
    ]);
}

public function exportExcel()
{
    return $this->service->exportExcel();
}

public function importExcel(Request $request): JsonResponse
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
    ]);

    $result = $this->service->importExcel($request->file('file'));

    return response()->json([
        'message' => 'Items imported successfully.',
        'data' => $result,
    ]);
}
}