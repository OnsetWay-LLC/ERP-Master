<?php

namespace App\Http\Controllers\Api\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrder\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrder\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchaseOrderService $service
    ) {}

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Purchase order created successfully as draft.',
            'data' => new PurchaseOrderResource($data),
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return new PurchaseOrderResource(
            $purchaseOrder->load([
                'supplier',
                'items.item',
                'items.targetWarehouse',
                'taxes.account',
                'fees.account',
            ])
        );
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $data = $this->service->update($purchaseOrder, $request->validated());

        return response()->json([
            'message' => 'Purchase order updated successfully.',
            'data' => new PurchaseOrderResource($data),
        ]);
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        $data = $this->service->submit($purchaseOrder);

        return response()->json([
            'message' => 'Purchase order confirmed successfully.',
            'data' => new PurchaseOrderResource($data),
        ]);
    }

public function cancel(PurchaseOrder $purchaseOrder)
{
    $data = $this->service->cancel($purchaseOrder);

    return response()->json([
        'message' => 'Purchase order cancelled successfully.',
        'data' => new PurchaseOrderResource($data),
    ]);
}
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->service->delete($purchaseOrder);

        return response()->json([
            'message' => 'Purchase order deleted successfully.',
        ]);
    }
}