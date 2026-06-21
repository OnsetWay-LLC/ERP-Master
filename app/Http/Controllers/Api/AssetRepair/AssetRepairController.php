<?php

namespace App\Http\Controllers\Api\AssetRepair;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRepair\StoreAssetRepairRequest;
use App\Http\Requests\AssetRepair\UpdateAssetRepairRequest;
use App\Http\Resources\AssetRepair\AssetRepairResource;
use App\Models\AssetRepair;
use App\Services\AssetRepair\AssetRepairService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class AssetRepairController extends Controller
{
    public function __construct(
        private readonly AssetRepairService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => AssetRepairResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreAssetRepairRequest $request): JsonResponse
    {
        try {
            $repair = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset repair created successfully.',
                'data' => new AssetRepairResource($repair),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(AssetRepair $assetRepair): JsonResponse
    {
        if ((int) $assetRepair->company_id !== $this->companyId()) {
            abort(404);
        }

        return response()->json([
            'status' => true,
            'data' => new AssetRepairResource(
                $assetRepair->load([
                    'asset.assetItem',
                    'asset.assetCategory',
                    'asset.location',
                    'items.purchaseInvoice',
                    'items.expenseAccount',
                    'items.paymentAccount',
                    'journalEntry.lines.account',
                ])
            ),
        ]);
    }

    public function update(UpdateAssetRepairRequest $request, AssetRepair $assetRepair): JsonResponse
    {
        if ((int) $assetRepair->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update($assetRepair, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Asset repair updated successfully.',
                'data' => new AssetRepairResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(AssetRepair $assetRepair): JsonResponse
    {
        if ((int) $assetRepair->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $submitted = $this->service->submit($assetRepair, auth('api')->id());

            return response()->json([
                'status' => true,
                'message' => 'Asset repair submitted successfully.',
                'data' => new AssetRepairResource($submitted),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AssetRepair $assetRepair): JsonResponse
    {
        if ((int) $assetRepair->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($assetRepair);

            return response()->json([
                'status' => true,
                'message' => 'Asset repair deleted successfully.',
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

    public function purchaseInvoices(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availablePurchaseInvoices($this->companyId()),
        ]);
    }
}