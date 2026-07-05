<?php

namespace App\Services\Reports;

use App\Models\SalesInvoice;

class AccountsReceivableReportService
{
    public function generate(array $filters): array
    {
        $query = SalesInvoice::query()
            ->with(['customer', 'receivableAccount'])
            ->where('status', 'submitted');

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $rows = $query
            ->orderBy('posting_date')
            ->orderBy('id')
            ->get()
            ->map(function ($invoice) {
                return [
                    'posting_date' => $invoice->posting_date,
                    'party_type' => 'Customer',
                    'party' => app()->getLocale() === 'ar'
                        ? ($invoice->customer?->name_ar ?? $invoice->customer?->name_en)
                        : ($invoice->customer?->name_en ?? $invoice->customer?->name_ar),

                    'receivable_account' => app()->getLocale() === 'ar'
                        ? ($invoice->receivableAccount?->name_ar ?? $invoice->receivableAccount?->name_en)
                        : ($invoice->receivableAccount?->name_en ?? $invoice->receivableAccount?->name_ar),

                    'voucher_type' => 'Sales Invoice',
                    'voucher_no' => $invoice->invoice_number,
                    'due_date' => $invoice->payment_due_date,

                    'invoice_amount' => (float) $invoice->grand_total,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'credit_note' => (float) $invoice->credit_note_amount,
                    'outstanding' => round(
                         (float) $invoice->grand_total
                          - (float) $invoice->paid_amount
                           - abs((float) $invoice->credit_note_amount), 2),
                ];
            })
            ->values();

        $totals = [
            'invoice_amount' => round($rows->sum('invoice_amount'), 2),
            'paid_amount' => round($rows->sum('paid_amount'), 2),
            'credit_note' => round($rows->sum('credit_note'), 2),
            'outstanding' => round($rows->sum('outstanding'), 2),
        ];

        return [
            'rows' => $rows->toArray(),
            'totals' => $totals,
        ];
    }
}