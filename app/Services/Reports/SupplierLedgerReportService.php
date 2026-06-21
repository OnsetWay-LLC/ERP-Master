<?php

namespace App\Services\Reports;

use App\Models\PaymentEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Supplier;

class SupplierLedgerReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        $suppliers = Supplier::query()
            ->orderBy('id')
            ->get();

        $rows = $suppliers->map(function ($supplier) use ($fromDate, $toDate) {
            $locale = app()->getLocale();

            $supplierName = $locale === 'ar'
                ? ($supplier->supplier_name_ar ?? $supplier->supplier_name_en)
                : ($supplier->supplier_name_en ?? $supplier->supplier_name_ar);

            $openingBalance = (float) ($supplier->opening_balance ?? 0);

            $invoiceAmount = (float) PurchaseInvoice::query()
                ->where('supplier_id', $supplier->id)
                ->where('status', 'submitted')
                ->whereBetween('posting_date', [$fromDate, $toDate])
                ->sum('grand_total');

            $paidAmount = (float) PaymentEntry::query()
                ->where('supplier_id', $supplier->id)
                ->where('status', 'submitted')
                ->whereBetween('posting_date', [$fromDate, $toDate])
                ->sum('paid_amount');

            $debitNote = (float) PurchaseReturn::query()
                ->where('supplier_id', $supplier->id)
                ->where('status', 'submitted')
                ->whereBetween('posting_date', [$fromDate, $toDate])
                ->sum('grand_total');

            $debitNote = abs($debitNote);

            $closingBalance = round(
                $openingBalance + $invoiceAmount - $paidAmount - $debitNote,
                2
            );

            $drCr = '';
            $displayClosingBalance = $closingBalance;

            if ($closingBalance > 0) {
                $drCr = 'Cr';
            } elseif ($closingBalance < 0) {
                $drCr = 'Dr';
                $displayClosingBalance = abs($closingBalance);
            }

            return [
                'supplier' => $supplierName,
                'opening_balance' => round($openingBalance, 2),
                'invoice_amount' => round($invoiceAmount, 2),
                'paid_amount' => round($paidAmount, 2),
                'debit_note' => round($debitNote, 2),
                'closing_balance' => round($displayClosingBalance, 2),
                'dr_cr' => $drCr,
                'raw_closing_balance' => $closingBalance,
            ];
        })->filter(function ($row) {
            return $row['opening_balance'] != 0
                || $row['invoice_amount'] != 0
                || $row['paid_amount'] != 0
                || $row['debit_note'] != 0
                || $row['raw_closing_balance'] != 0;
        })->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'opening_balance' => round($rows->sum('opening_balance'), 2),
                'invoice_amount' => round($rows->sum('invoice_amount'), 2),
                'paid_amount' => round($rows->sum('paid_amount'), 2),
                'debit_note' => round($rows->sum('debit_note'), 2),
                'closing_balance' => round($rows->sum('raw_closing_balance'), 2),
            ],
        ];
    }
}