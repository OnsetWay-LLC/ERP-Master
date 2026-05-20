<?php

namespace App\Http\Controllers\Api\StockEntry;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockEntry\StoreStockEntryRequest;
use App\Http\Requests\StockEntry\UpdateStockEntryRequest;
use App\Http\Resources\StockEntry\StockEntryResource;
use App\Services\StockEntry\StockEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StockEntryController extends Controller
{
    public function __construct(
        private readonly StockEntryService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $entries = $this->service->getAll(
            $this->companyId(),
            $request->only([
                'entry_type',
                'status',
            ])
        );

        return response()->json([
            'status' => true,
            'data' => StockEntryResource::collection($entries),
        ]);
    }

    public function store(StoreStockEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Stock entry draft created successfully.',
                'data' => new StockEntryResource($entry),
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
        $entry = $this->service->show(
            $this->companyId(),
            $id
        );

        return response()->json([
            'status' => true,
            'data' => new StockEntryResource($entry),
        ]);
    }

    public function update(UpdateStockEntryRequest $request, int $id): JsonResponse
    {
        try {
            $entry = $this->service->updateDraft(
                $this->companyId(),
                $id,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Stock entry draft updated successfully.',
                'data' => new StockEntryResource($entry),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(int $id): JsonResponse
    {
        try {
            $entry = $this->service->submit(
                $this->companyId(),
                $id
            );

            return response()->json([
                'status' => true,
                'message' => 'Stock entry submitted successfully.',
                'data' => new StockEntryResource($entry),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $entry = $this->service->cancel(
                $this->companyId(),
                $id
            );

            return response()->json([
                'status' => true,
                'message' => 'Stock entry cancelled successfully.',
                'data' => new StockEntryResource($entry),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}