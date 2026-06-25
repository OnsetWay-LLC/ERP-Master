<?php

namespace App\Services\Reports;

use App\Models\JournalEntryLine;
use App\Models\PaymentEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use Illuminate\Support\Collection;

class AccountsPayableReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $supplierId = $filters['supplier_id'] ?? null;

        $invoiceRows = $this->purchaseInvoiceRows($fromDate, $toDate, $supplierId);
        $paymentRows = $this->paymentEntryRows($fromDate, $toDate, $supplierId);
        $debitNoteRows = $this->debitNoteRows($fromDate, $toDate, $supplierId);
      

        $rows = collect()
            ->merge($invoiceRows)
            ->merge($paymentRows)
            ->merge($debitNoteRows)
            
            ->sortBy([
                ['posting_date', 'asc'],
                ['voucher_no', 'asc'],
            ])
            ->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'invoice_amount' => round($rows->sum('invoice_amount'), 2),
                'paid_amount' => round($rows->sum('paid_amount'), 2),
                'debit_note' => round($rows->sum('debit_note'), 2),
                'outstanding' => round($rows->sum('outstanding'), 2),
            ],
        ];
    }

    private function purchaseInvoiceRows(string $fromDate, string $toDate, ?int $supplierId): Collection
    {
        $query = PurchaseInvoice::query()
            ->with(['supplier', 'supplierPayableAccount'])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get()->map(function ($invoice) {
            $locale = app()->getLocale();

            $supplierName = $locale === 'ar'
                ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
                : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar);

            $payableAccount = $locale === 'ar'
                ? ($invoice->supplierPayableAccount?->name_ar ?? $invoice->supplierPayableAccount?->name_en)
                : ($invoice->supplierPayableAccount?->name_en ?? $invoice->supplierPayableAccount?->name_ar);

            $invoiceAmount = (float) $invoice->grand_total;
            $paidAmount = (float) $invoice->paid_amount;
            $debitNote = (float) $invoice->returned_amount;

            return [
                'posting_date' => $invoice->posting_date,
                'party_type' => 'Supplier',
                'party' => $supplierName,
                'payable_account' => $payableAccount,
                'voucher_type' => 'Purchase Invoice',
                'voucher_no' => $invoice->invoice_number,
                'due_date' => $invoice->due_date,
                'invoice_amount' => round($invoiceAmount, 2),
                'paid_amount' => round($paidAmount, 2),
                'debit_note' => round($debitNote, 2),
                'outstanding' => round($invoiceAmount - $paidAmount - $debitNote, 2),
            ];
        });
    }

    private function paymentEntryRows(string $fromDate, string $toDate, ?int $supplierId): Collection
    {
        $query = PaymentEntry::query()
            ->with(['supplier', 'payableAccount'])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get()->map(function ($payment) {
            $locale = app()->getLocale();

            $supplierName = $locale === 'ar'
                ? ($payment->supplier?->supplier_name_ar ?? $payment->supplier?->supplier_name_en)
                : ($payment->supplier?->supplier_name_en ?? $payment->supplier?->supplier_name_ar);

            $payableAccount = $locale === 'ar'
                ? ($payment->payableAccount?->name_ar ?? $payment->payableAccount?->name_en)
                : ($payment->payableAccount?->name_en ?? $payment->payableAccount?->name_ar);

            return [
                'posting_date' => $payment->posting_date,
                'party_type' => 'Supplier',
                'party' => $supplierName,
                'payable_account' => $payableAccount,
                'voucher_type' => 'Payment Entry',
                'voucher_no' => $payment->series,
                'due_date' => null,
                'invoice_amount' => 0,
                'paid_amount' => round((float) $payment->paid_amount, 2),
                'debit_note' => 0,
                'outstanding' => -round((float) $payment->paid_amount, 2),
            ];
        });
    }

    private function debitNoteRows(string $fromDate, string $toDate, ?int $supplierId): Collection
    {
        $query = PurchaseReturn::query()
            ->with(['supplier', 'supplierAccount'])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get()->map(function ($return) {
            $locale = app()->getLocale();

            $supplierName = $locale === 'ar'
                ? ($return->supplier?->supplier_name_ar ?? $return->supplier?->supplier_name_en)
                : ($return->supplier?->supplier_name_en ?? $return->supplier?->supplier_name_ar);

            $payableAccount = $locale === 'ar'
                ? ($return->supplierAccount?->name_ar ?? $return->supplierAccount?->name_en)
                : ($return->supplierAccount?->name_en ?? $return->supplierAccount?->name_ar);

            $amount = abs((float) $return->grand_total);

            return [
                'posting_date' => $return->posting_date,
                'party_type' => 'Supplier',
                'party' => $supplierName,
                'payable_account' => $payableAccount,
                'voucher_type' => 'Debit Note',
                'voucher_no' => $return->series,
                'due_date' => $return->payment_due_date,
                'invoice_amount' => 0,
                'paid_amount' => 0,
                'debit_note' => round($amount, 2),
                'outstanding' => -round($amount, 2),
            ];
        });
    }

   
}