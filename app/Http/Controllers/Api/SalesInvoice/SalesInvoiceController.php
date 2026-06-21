<?php

namespace App\Http\Controllers\Api\SalesInvoice;


use App\Http\Controllers\Controller;
use App\Http\Requests\SalesInvoice\StoreSalesInvoiceRequest;
use App\Http\Requests\SalesInvoice\UpdateSalesInvoiceRequest;
use App\Http\Resources\SalesInvoice\SalesInvoiceResource;
use App\Models\SalesInvoice;
use App\Models\DeliveryNote;
use App\Services\SalesInvoice\SalesInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NumberToWords\NumberToWords;
use Mpdf\Mpdf;

class SalesInvoiceController extends Controller
{
    public function __construct(
        private readonly SalesInvoiceService $service
    ) {}

    public function store(StoreSalesInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->service->create($request->validated());
        $invoice->load('pendingDiscountApproval');

        return response()->json([
            'message' => $invoice->pendingDiscountApproval
                ? 'Sales invoice created as draft. Discount approval request has been sent to CFO.'
                : 'Sales invoice created successfully as draft.',
            'data' => new SalesInvoiceResource($invoice),
        ], 201);
    }

    public function update(
        UpdateSalesInvoiceRequest $request,
        SalesInvoice $salesInvoice
    ): JsonResponse {
        $invoice = $this->service->update($salesInvoice, $request->validated());
        $invoice->load('pendingDiscountApproval');

        return response()->json([
            'message' => $invoice->pendingDiscountApproval
                ? 'Sales invoice updated. Discount approval request has been sent to CFO.'
                : 'Sales invoice updated successfully.',
            'data' => new SalesInvoiceResource($invoice),
        ]);
    }

    public function submit(SalesInvoice $salesInvoice): JsonResponse
    {
        $invoice = $this->service->submit($salesInvoice);

        return response()->json([
            'message' => 'Sales invoice posted and submitted to general ledger successfully.',
            'data' => new SalesInvoiceResource($invoice),
        ]);
    }

    public function printPreview(SalesInvoice $salesInvoice): JsonResponse
    {
        $salesInvoice->load(['customer', 'items', 'taxes', 'fees']);

        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('en');

        $amountInWords = strtoupper(
            $transformer->toWords((int) $salesInvoice->grand_total)
        ) . ' JOD ONLY';

        return response()->json([
            'invoice' => new SalesInvoiceResource($salesInvoice),
            'amount_in_words' => $amountInWords,
        ]);
    }

    public function showHtml(SalesInvoice $salesInvoice, Request $request)
    {
        $salesInvoice->load([
            'company',
            'customer',
            'items.item',
            'taxes',
            'fees',
        ]);

        $locale = $request->query('locale')
            ?? $request->getPreferredLanguage(['ar', 'en', 'both'])
            ?? 'both';

        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('en');

        $amountInWords = strtoupper(
            $transformer->toWords((int) $salesInvoice->grand_total)
        ) . ' JOD ONLY';

        return view('invoices.template', [
            'invoice' => $salesInvoice,
            'amountInWords' => $amountInWords,
            'locale' => $locale,
        ]);
    }

    public function downloadPdf(SalesInvoice $salesInvoice, Request $request): Response
    {
        $salesInvoice->load([
            'company',
            'customer',
            'items.item',
            'taxes',
            'fees',
        ]);

        $locale = $request->query('locale')
            ?? $request->getPreferredLanguage(['ar', 'en', 'both'])
            ?? 'both';

        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('en');

        $amountInWords = strtoupper(
            $transformer->toWords((int) $salesInvoice->grand_total)
        ) . ' JOD ONLY';

        $html = view('invoices.template', [
            'invoice' => $salesInvoice,
            'amountInWords' => $amountInWords,
            'locale' => $locale,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'direction' => $locale === 'en' ? 'ltr' : 'rtl',
        ]);

        $mpdf->WriteHTML($html);

        $filename = $salesInvoice->invoice_number . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
public function createFromDeliveryNote(DeliveryNote $deliveryNote): JsonResponse
{
    $invoice = $this->service->createFromDeliveryNote($deliveryNote);

    return response()->json([
        'message' => 'Sales invoice created from delivery note successfully.',
        'data' => new SalesInvoiceResource($invoice),
    ], 201);
}
    public function destroy(SalesInvoice $salesInvoice): JsonResponse
    {
        $this->service->delete($salesInvoice);

        return response()->json([
            'message' => 'Sales invoice deleted successfully.',
        ]);
    }
}
