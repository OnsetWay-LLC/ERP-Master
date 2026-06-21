<?php

namespace App\Http\Controllers\Api\AssetSale;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetSale\StoreAssetSaleRequest;
use App\Http\Requests\AssetSale\UpdateAssetSaleRequest;
use App\Http\Resources\AssetSale\AssetSaleResource;
use App\Models\AssetSale;
use App\Services\AssetSale\AssetSaleService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class AssetSaleController extends Controller
{
    public function __construct(
        private readonly AssetSaleService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => AssetSaleResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreAssetSaleRequest $request): JsonResponse
    {
        try {
            $sale = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Asset sale created successfully.',
                'data' => new AssetSaleResource($sale),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(AssetSale $assetSale): JsonResponse
    {
        if ((int) $assetSale->company_id !== $this->companyId()) {
            abort(404);
        }

        return response()->json([
            'status' => true,
            'data' => new AssetSaleResource(
                $assetSale->load([
                    'asset.assetItem',
                    'asset.assetCategory',
                    'asset.location',
                    'assetCategory',
                    'warehouse',
                    'journalEntry.lines.account',
                ])
            ),
        ]);
    }

    public function update(UpdateAssetSaleRequest $request, AssetSale $assetSale): JsonResponse
    {
        if ((int) $assetSale->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update($assetSale, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Asset sale updated successfully.',
                'data' => new AssetSaleResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(AssetSale $assetSale): JsonResponse
    {
        if ((int) $assetSale->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $submitted = $this->service->submit($assetSale, auth('api')->id());

            return response()->json([
                'status' => true,
                'message' => 'Asset sale submitted successfully.',
                'data' => new AssetSaleResource($submitted),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AssetSale $assetSale): JsonResponse
    {
        if ((int) $assetSale->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($assetSale);

            return response()->json([
                'status' => true,
                'message' => 'Asset sale deleted successfully.',
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

    public function warehouses(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availableWarehouses($this->companyId()),
        ]);
    }

    public function accounts(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->availableAccounts($this->companyId()),
        ]);
    }
    public function createInvoice(StoreAssetSaleRequest $request): JsonResponse
{
    try {
        $sale = $this->service->createSalesInvoice(
            $request->validated(),
            $this->companyId(),
            auth('api')->id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Asset sale invoice created successfully.',
            'data' => new AssetSaleResource($sale),
        ], 201);
    } catch (InvalidArgumentException|\RuntimeException $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 422);
    }
}
}