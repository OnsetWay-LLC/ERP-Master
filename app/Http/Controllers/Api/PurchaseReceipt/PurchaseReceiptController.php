<?php 
namespace App\Http\Controllers\Api\PurchaseReceipt;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseReceipt\StorePurchaseReceiptRequest;
use App\Services\PurchaseReceipt\PurchaseReceiptService;

class PurchaseReceiptController extends Controller
{
    public function __construct(
        private PurchaseReceiptService $service
    ) {}

    public function store(StorePurchaseReceiptRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => true,
            'data' => $data
        ], 201);
    }
    public function update(StorePurchaseReceiptRequest $request, $id)
{
    $receipt = PurchaseReceipt::findOrFail($id);

    $data = $this->service->update($receipt, $request->validated());

    return response()->json([
        'status' => true,
        'message' => 'Updated successfully',
        'data' => $data,
    ]);
}
}