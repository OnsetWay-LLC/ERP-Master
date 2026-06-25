<?php

namespace App\Services\Reports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;

class PurchaseRegisterReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $supplierId = $filters['supplier_id'] ?? null;

        $invoiceRows = $this->getPurchaseInvoiceRows($fromDate, $toDate, $supplierId);
        $returnRows = $this->getPurchaseReturnRows($fromDate, $toDate, $supplierId);

        $rows = collect()
            ->merge($invoiceRows)
            ->merge($returnRows)
            ->sortBy([
                ['posting_date', 'asc'],
                ['voucher', 'asc'],
            ])
            ->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'stock_account' => round($rows->sum('stock_account'), 2),
                'vat' => round($rows->sum('vat'), 2),
                'fees' => round($rows->sum('fees'), 2),
                'discount' => round($rows->sum('discount'), 2),
                'net_total' => round($rows->sum('net_total'), 2),
                'grand_total' => round($rows->sum('grand_total'), 2),
                'outstanding' => round($rows->sum('outstanding'), 2),
            ],
        ];
    }

    private function getPurchaseInvoiceRows(
        string $fromDate,
        string $toDate,
        ?int $supplierId
    ) {
        $query = PurchaseInvoice::query()
            ->with([
                'supplier',
                'purchaseOrder',
                'supplierPayableAccount',
                'stockAccount',
            ])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query
            ->orderBy('posting_date')
            ->orderBy('id')
            ->get()
            ->map(function ($invoice) {
    $locale = app()->getLocale();

    $discount = abs((float) $invoice->discount_amount);
    $totalAmount = (float) $invoice->net_total;

    $calculatedNetTotal = $discount > 0
        ? $totalAmount - $discount
        : $totalAmount;

    return [
        'voucher_type' => 'Purchase Invoice',
        'voucher' => $invoice->invoice_number,
        'posting_date' => $invoice->posting_date,

        'supplier_name' => $locale === 'ar'
            ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
            : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar),

        'payable_account' => $locale === 'ar'
            ? ($invoice->supplierPayableAccount?->name_ar ?? $invoice->supplierPayableAccount?->name_en)
            : ($invoice->supplierPayableAccount?->name_en ?? $invoice->supplierPayableAccount?->name_ar),

        'purchase_order' => $invoice->purchaseOrder?->series ?? '-',

        'stock_account' => (float) $invoice->net_total,
        'vat' => (float) $invoice->tax_total,
        'fees' => (float) $invoice->fees_total,
        'discount' => (float) $invoice->discount_amount,
        'net_total' => $calculatedNetTotal,
        'grand_total' => (float) $invoice->grand_total,
        'outstanding' => (float) $invoice->outstanding_amount,
    ];
});
             ;
    }

    private function getPurchaseReturnRows(
        string $fromDate,
        string $toDate,
        ?int $supplierId
    ) {
        $query = PurchaseReturn::query()
            ->with([
                'supplier',
                'purchaseInvoice',
                'purchaseAccount',
                'supplierAccount',
            ])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query
            ->orderBy('posting_date')
            ->orderBy('id')
            ->get()
            ->map(function ($return) {
                $locale = app()->getLocale();

                return [
                    'voucher_type' => 'Debit Note',
                    'voucher' => $return->series,
                    'posting_date' => $return->posting_date,

                    'supplier_name' => $locale === 'ar'
                        ? ($return->supplier?->supplier_name_ar ?? $return->supplier?->supplier_name_en)
                        : ($return->supplier?->supplier_name_en ?? $return->supplier?->supplier_name_ar),

                    'payable_account' => $locale === 'ar'
                        ? ($return->supplierAccount?->name_ar ?? $return->supplierAccount?->name_en)
                        : ($return->supplierAccount?->name_en ?? $return->supplierAccount?->name_ar),

                    'purchase_order' => $return->purchaseInvoice?->invoice_number ?? '-',

                    'stock_account' => (float) $return->net_total,
                    'vat' => (float) $return->tax_total,
                    'fees' => (float) $return->fees_total,
                    'discount' => (float) $return->additional_discount_amount,
                    'net_total' => (float) $return->net_total,
                    'grand_total' => (float) $return->grand_total,
                    'outstanding' => (float) $return->grand_total,
                ];
            });
    }
}