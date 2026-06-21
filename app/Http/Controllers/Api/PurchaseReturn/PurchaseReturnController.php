<?php

namespace App\Http\Controllers\Api\PurchaseReturn;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseReturns\StorePurchaseReturnRequest;
use App\Http\Requests\PurchaseReturns\UpdatePurchaseReturnRequest;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturns\PurchaseReturnService;
use App\Models\PurchaseInvoice;
use Illuminate\Http\JsonResponse;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private PurchaseReturnService $service
    ) {
    }

    public function store(
        StorePurchaseReturnRequest $request
    ): JsonResponse {
        $purchaseReturn = $this->service->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Purchase Return created successfully.',
            'data' => $purchaseReturn,
        ], 201);
    }

    public function update(
        UpdatePurchaseReturnRequest $request,
        PurchaseReturn $purchaseReturn
    ): JsonResponse {
        $purchaseReturn = $this->service->update(
            $purchaseReturn,
            $request->validated()
        );

        return response()->json([
            'message' => 'Purchase Return updated successfully.',
            'data' => $purchaseReturn,
        ]);
    }

    public function show(
        PurchaseReturn $purchaseReturn
    ): JsonResponse {
        return response()->json([
            'data' => $purchaseReturn->load([
                'purchaseInvoice',
                'supplier',
                'items.item',
                'taxes.accountHead',
                'fees.accountHead',
                'purchaseAccount',
                'supplierAccount',
                'journalEntry.lines.account',
                'creator',
            ]),
        ]);
    }

    public function submit(
        PurchaseReturn $purchaseReturn
    ): JsonResponse {
        $purchaseReturn = $this->service->submit(
            $purchaseReturn
        );

        return response()->json([
            'message' => 'Purchase Return submitted successfully.',
            'data' => $purchaseReturn,
        ]);
    }

    public function cancel(
        PurchaseReturn $purchaseReturn
    ): JsonResponse {
        $purchaseReturn = $this->service->cancel(
            $purchaseReturn
        );

        return response()->json([
            'message' => 'Purchase Return cancelled successfully.',
            'data' => $purchaseReturn,
        ]);
    }

    public function destroy(
        PurchaseReturn $purchaseReturn
    ): JsonResponse {
        $this->service->delete($purchaseReturn);

        return response()->json([
            'message' => 'Purchase Return deleted successfully.',
        ]);
    }
    public function getDataFromPurchaseInvoice(
    PurchaseInvoice $purchaseInvoice
): JsonResponse {
    $data = $this->service->getDataFromPurchaseInvoice($purchaseInvoice);

    return response()->json([
        'data' => $data,
    ]);
}
}