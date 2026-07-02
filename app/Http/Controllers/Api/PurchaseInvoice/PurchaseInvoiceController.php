<?php

namespace App\Http\Controllers\Api\PurchaseInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Http\Resources\PurchaseInvoice\PurchaseInvoiceResource;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReceipt;
use App\Services\PurchaseInvoice\PurchaseInvoiceService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use NumberFormatter;

class PurchaseInvoiceController extends Controller
{
    public function __construct(private PurchaseInvoiceService $service) {}

    public function index()
    {
        $invoices = PurchaseInvoice::with([
            'company',
            'supplier',
            'purchaseReceipt',
            'purchaseOrder',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
            'journalEntry',
        ])
            ->where('status', '!=', 'cancelled')
            ->latest('id')
            ->paginate(10);

        return PurchaseInvoiceResource::collection($invoices);
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
            $purchaseInvoice->load([
                'company',
                'supplier',
                'items.item',
                'items.warehouse',
                'taxes.account',
                'fees.account',
                'journalEntry',
            ])
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
public function storeManual(Request $request)
{
    $companyId = 1;

    $invoice = $this->service->createManual(
        $request->all(),
        $companyId
    );

    return new PurchaseInvoiceResource($invoice);
}

public function storeManualInventory(Request $request)
{
    $data = $request->validate([
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'posting_date' => ['nullable', 'date'],
        'posting_time' => ['nullable'],
        'due_date' => ['nullable', 'date'],
        'supplier_invoice_no' => ['nullable', 'string'],
        'supplier_invoice_date' => ['nullable', 'date'],

        'additional_discount_percentage' => ['nullable', 'numeric', 'min:0'],
        'additional_discount_amount' => ['nullable', 'numeric', 'min:0'],

        'items' => ['required', 'array', 'min:1'],
        'items.*.item_id' => ['required', 'exists:items,id'],
        'items.*.warehouse_id' => ['nullable', 'exists:warehouses,id'],
        'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        'items.*.rate' => ['required', 'numeric', 'gte:0'],

        'tax_template_ids' => ['nullable', 'array'],
        'fees_template_ids' => ['nullable', 'array'],
    ]);

    $invoice = app(PurchaseInvoiceService::class)
        ->createManualInventory($data, 1);

    return response()->json([
        'status' => true,
        'message' => 'Manual inventory purchase invoice created successfully.',
        'data' => $invoice,
    ]);
}
public function itemsWithoutStock(Request $request)
{
    $companyId = 1;

    $items = \App\Models\Item::query()
        ->where('company_id', $companyId)
        ->whereDoesntHave('warehouseStocks')
        ->whereNull('deleted_at')
        ->select([
            'id',
            'item_code',
            'name_ar',
            'name_en',
            'purchase_price',
            'selling_price',
            'barcode',
        ])
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'data' => $items,
    ]);
}
    public function update(StorePurchaseInvoiceRequest $request, $id)
    {
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

    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        $invoice = $this->service->cancel($purchaseInvoice);

        return response()->json([
            'message' => 'Purchase invoice cancelled successfully.',
            'data' => new PurchaseInvoiceResource($invoice),
        ]);
    }

    public function pdf(PurchaseInvoice $purchaseInvoice): Response
    {
        $purchaseInvoice->load([
            'company',
            'supplier',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
        ]);

        $isArabic = app()->getLocale() === 'ar';

        $companyName = $isArabic
            ? ($purchaseInvoice->company?->name_ar ?? $purchaseInvoice->company?->name_en)
            : ($purchaseInvoice->company?->name_en ?? $purchaseInvoice->company?->name_ar);

        $amountWords = $this->amountToWords(
            (float) $purchaseInvoice->grand_total,
            $isArabic
        );

        $html = view('purchase-invoices.pdf', [
            'invoice' => $purchaseInvoice,
            'companyName' => $companyName,
            'amountWords' => $amountWords,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('purchase-invoice.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="purchase-invoice.pdf"',
        ]);
    }

    private function amountToWords(float $amount, bool $isArabic): string
    {
        $integerPart = (int) floor($amount);
        $decimalPart = (int) round(($amount - $integerPart) * 100);

        $locale = $isArabic ? 'ar' : 'en';
        $formatter = new NumberFormatter($locale, NumberFormatter::SPELLOUT);

        $mainWords = $formatter->format($integerPart);
        $decimalWords = $decimalPart > 0 ? $formatter->format($decimalPart) : null;

        if ($isArabic) {
            return $decimalPart > 0
                ? "{$mainWords} دينار أردني و {$decimalWords} فلس فقط"
                : "{$mainWords} دينار أردني فقط";
        }

        return $decimalPart > 0
            ? ucfirst($mainWords) . " Jordanian Dinars and {$decimalWords} fils only"
            : ucfirst($mainWords) . " Jordanian Dinars only";
    }
    public function accounts()
{
    return $this->service->purchaseInvoiceAccounts();
}
}