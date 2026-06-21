<?php

namespace App\Http\Controllers\Api\PaymentEntry;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentEntries\StorePaymentEntryFromPurchaseInvoiceRequest;
use App\Http\Requests\PaymentEntries\UpdatePaymentEntryRequest;
use App\Models\PaymentEntry;
use App\Services\PaymentEntries\PaymentEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class PaymentEntryController extends Controller
{
    public function __construct(
        private PaymentEntryService $service
    ) {}

   public function storeFromPurchaseInvoice(
    StorePaymentEntryFromPurchaseInvoiceRequest $request
): JsonResponse {
    $paymentEntry = $this->service->createFromPurchaseInvoice($request->validated());

    return response()->json([
        'message' => 'Payment Entry created as draft successfully.',
        'data' => $paymentEntry,
    ], 201);
}
public function submit(PaymentEntry $paymentEntry): JsonResponse
{
    $paymentEntry = $this->service->submit($paymentEntry);

    return response()->json([
        'message' => 'Payment Entry submitted successfully.',
        'data' => $paymentEntry,
    ]);
}
    public function show(PaymentEntry $paymentEntry): JsonResponse
    {
        return response()->json([
            'data' => $paymentEntry->load([
                'supplier',
                'paidFromAccount',
                'payableAccount',
                'references.purchaseInvoice',
                'journalEntry.lines.account',
                'creator',
            ]),
        ]);
    }

    public function print(PaymentEntry $paymentEntry): Response
    {
        $paymentEntry->load([
            'supplier',
            'paidFromAccount',
            'payableAccount',
            'references.purchaseInvoice',
            'creator',
        ]);

        $html = view('payment-entries.print', [
            'paymentEntry' => $paymentEntry,
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

        return response($mpdf->Output('payment-entry.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="payment-entry.pdf"',
        ]);
    }
    public function update(
    UpdatePaymentEntryRequest $request,
    PaymentEntry $paymentEntry
): JsonResponse {
    $paymentEntry = $this->service->update($paymentEntry, $request->validated());

    return response()->json([
        'message' => 'Payment Entry updated successfully.',
        'data' => $paymentEntry,
    ]);
}

public function cancel(PaymentEntry $paymentEntry): JsonResponse
{
    $paymentEntry = $this->service->cancel($paymentEntry);

    return response()->json([
        'message' => 'Payment Entry cancelled successfully.',
        'data' => $paymentEntry,
    ]);
}
public function destroy(PaymentEntry $paymentEntry): JsonResponse
{
    $this->service->delete($paymentEntry);

    return response()->json([
        'message' => 'Payment Entry deleted successfully.'
    ]);
}
}