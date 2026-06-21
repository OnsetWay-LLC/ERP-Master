<?php

namespace app\Http\Controllers\Api\PurchaseInvoice;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseInvoice\PurchaseInvoiceResource;
use App\Models\PurchaseInvoice;
use App\Services\PurchaseInvoice\PurchaseInvoiceService;
use App\Http\Requests\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Models\PurchaseReceipt;
use Illuminate\Http\Response;
use Mpdf\Mpdf;
class PurchaseInvoiceController extends Controller
{
    public function __construct(private PurchaseInvoiceService $service) {}

    public function index()
{
    $invoices = PurchaseInvoice::with([
        'supplier',
        'purchaseReceipt',
        'purchaseOrder',
        'items.item',
        'taxes.account',
        'fees.account',
        'journalEntry',
    ])
    ->where('status', '!=', 'cancelled')
    ->latest('id')
    ->paginate(10);

    return response()->json([
        'status' => true,
        'data' => PurchaseInvoiceResource::collection($invoices),
    ]);
}
    public function store(StorePurchaseInvoiceRequest $request)
    {
        $receipt = PurchaseReceipt::findOrFail($request->purchase_receipt_id);

        $invoice = $this->service->createFromPurchaseReceipt(
            $receipt,
            $request->validated()
        );

        return response()->json([
            'message' => 'Purchase invoice created successfully as draft.',
            'data' => new PurchaseInvoiceResource($invoice),
        ], 201);
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return new PurchaseInvoiceResource(
            $purchaseInvoice->load(['supplier','items','taxes.account','fees.account','journalEntry'])
        );
    }

    public function submit(PurchaseInvoice $purchaseInvoice)
    {
        $invoice = $this->service->submit($purchaseInvoice);

        return response()->json([
            'message' => 'Purchase invoice submitted successfully.',
            'data' => new PurchaseInvoiceResource($invoice),
        ]);
    }
public function update(
    StorePurchaseInvoiceRequest $request,
    $id
) {
    $invoice = PurchaseInvoice::findOrFail($id);

    $data = $this->service->update(
        $invoice,
        $request->validated()
    );

    return response()->json([
        'status' => true,
        'message' => 'Purchase Invoice updated successfully.',
        'data' => $data,
    ]);
}
public function pdf(PurchaseInvoice $purchaseInvoice): Response
{
    $purchaseInvoice->load([
        'supplier',
        'items.item',
        'items.warehouse',
        'taxes.account',
        'fees.account',
    ]);

    $html = view('purchase-invoices.pdf', [
        'invoice' => $purchaseInvoice,
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('purchase-invoice.pdf', 'S'), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="purchase-invoice.pdf"',
    ]);
}
    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        $invoice = $this->service->cancel($purchaseInvoice);

        return response()->json([
            'message' => 'Purchase invoice cancelled successfully.',
            'data' => new PurchaseInvoiceResource($invoice),
        ]);
    }
}