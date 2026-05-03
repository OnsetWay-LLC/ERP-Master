<?php

namespace App\Http\Controllers\Api\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
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
            'status' => true,
            'data' => $data
        ], 201);
    }

    public function submit($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $data = $this->service->submit($po);

        return response()->json([
            'status' => true,
            'message' => 'Submitted successfully',
            'data' => $data
        ]);
    }
}