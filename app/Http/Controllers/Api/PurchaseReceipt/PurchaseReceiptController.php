<?php

namespace App\Http\Controllers\Api\PurchaseReceipt;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseReceipt\StorePurchaseReceiptRequest;
 use App\Http\Requests\PurchaseReceipt\UpdatePurchaseReceiptRequest;
use App\Http\Resources\PurchaseReceipt\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use App\Services\PurchaseReceipt\PurchaseReceiptService;

class PurchaseReceiptController extends Controller
{
    public function __construct(
        private PurchaseReceiptService $service
    ) {}

   public function index()
{
    return PurchaseReceiptResource::collection(
        $this->service->getAll()
    );
}

    public function store(StorePurchaseReceiptRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Purchase Receipt draft created successfully.',
            'data' => new PurchaseReceiptResource($data),
        ], 201);
    }

    public function show(PurchaseReceipt $purchaseReceipt)
    {
        return new PurchaseReceiptResource(
            $purchaseReceipt->load([
                'purchaseOrder',
                'supplier',
                'items.item',
                'items.acceptedWarehouse',
                'items.rejectedWarehouse',
                'taxes.account',
                'fees.account',
                'creator',
            ])
        );
    }

    public function update(UpdatePurchaseReceiptRequest $request, PurchaseReceipt $purchaseReceipt)
    {
        $data = $this->service->update($purchaseReceipt, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Purchase Receipt updated successfully.',
            'data' => new PurchaseReceiptResource($data),
        ]);
    }

    public function submit(PurchaseReceipt $purchaseReceipt)
    {
        $data = $this->service->submit($purchaseReceipt);

        return response()->json([
            'status' => true,
            'message' => 'Purchase Receipt submitted successfully.',
            'data' => new PurchaseReceiptResource($data),
        ]);
    }

    public function cancel(PurchaseReceipt $purchaseReceipt)
    {
        $data = $this->service->cancel($purchaseReceipt);

        return response()->json([
            'status' => true,
            'message' => 'Purchase Receipt cancelled successfully.',
            'data' => new PurchaseReceiptResource($data),
        ]);
    }
}