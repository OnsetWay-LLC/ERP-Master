<?php

namespace App\Services\Reports;

use App\Models\SalesInvoice;
use App\Models\SalesReturn;

class SalesRegisterReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $customerId = $filters['customer_id'] ?? null;

        $invoiceRows = $this->invoiceRows($fromDate, $toDate, $customerId);
        $returnRows = $this->returnRows($fromDate, $toDate, $customerId);

        $rows = collect()
            ->merge($invoiceRows)
            ->merge($returnRows)
            ->sortBy([
                ['posting_date', 'asc'],
                ['voucher', 'asc'],
            ])
            ->values();

        $totals = [
            'sales_account' => round($rows->sum('sales_account'), 2),
            'vat' => round($rows->sum('vat'), 2),
            'fees' => round($rows->sum('fees'), 2),
            'discount' => round($rows->sum('discount'), 2),
            'net_total' => round($rows->sum('net_total'), 2),
            'grand_total' => round($rows->sum('grand_total'), 2),
            'outstanding' => round($rows->sum('outstanding'), 2),
        ];

        return [
            'rows' => $rows->toArray(),
            'totals' => $totals,
        ];
    }

    private function invoiceRows(string $fromDate, string $toDate, ?int $customerId)
    {
        $query = SalesInvoice::query()
            ->with([
                'customer',
                'creator',
                'salesOrder',
                'items.warehouse',
                'receivableAccount',
            ])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->get()->map(function ($invoice) {
            $locale = app()->getLocale();

            $warehouseNames = $invoice->items
                ->pluck('warehouse')
                ->filter()
                ->map(function ($warehouse) use ($locale) {
                    return $locale === 'ar'
                        ? ($warehouse->name_ar ?? $warehouse->name_en)
                        : ($warehouse->name_en ?? $warehouse->name_ar);
                })
                ->unique()
                ->values()
                ->implode(', ');

            return [
                'voucher_type' => 'Sales Invoice',
                'voucher' => $invoice->invoice_number,
                'posting_date' => $invoice->posting_date,

                'customer_name' => $locale === 'ar'
                    ? ($invoice->customer?->name_ar ?? $invoice->customer?->name_en)
                    : ($invoice->customer?->name_en ?? $invoice->customer?->name_ar),

                'receivable_account' => $locale === 'ar'
                    ? ($invoice->receivableAccount?->name_ar ?? $invoice->receivableAccount?->name_en)
                    : ($invoice->receivableAccount?->name_en ?? $invoice->receivableAccount?->name_ar),

                'owner' => $invoice->creator?->name ?? $invoice->creator?->email ?? '-',

                'sales_order' => $invoice->salesOrder?->order_number ?? '-',
                'warehouse' => $warehouseNames ?: '-',

                'sales_account' => (float) $invoice->items->sum('amount'),
                'vat' => (float) $invoice->tax_total,
                'fees' => (float) $invoice->fees_total,
                'discount' => (float) $invoice->discount_amount,
                'net_total' => (float) ((float) $invoice->items->sum('amount') - (float) $invoice->discount_amount),
                'grand_total' => (float) $invoice->grand_total,
                'outstanding' => (float) $invoice->outstanding_amount,
                
            ];
        });
    }

  private function returnRows(string $fromDate, string $toDate, ?int $customerId)
{
    $query = SalesReturn::query()
        ->with([
            'customer',
            'creator',
            'salesInvoice',
            'items.warehouse',
            'salesInvoice.receivableAccount',
        ])
        ->where('status', 'submitted')
        ->whereBetween('posting_date', [$fromDate, $toDate]);

    if ($customerId) {
        $query->where('customer_id', $customerId);
    }

    return $query->get()->map(function ($return) {
        $locale = app()->getLocale();

        $returnItemTotal = (float) $return->items->sum('amount');

        $warehouseNames = $return->items
            ->pluck('warehouse')
            ->filter()
            ->map(function ($warehouse) use ($locale) {
                return $locale === 'ar'
                    ? ($warehouse->name_ar ?? $warehouse->name_en)
                    : ($warehouse->name_en ?? $warehouse->name_ar);
            })
            ->unique()
            ->values()
            ->implode(', ');

        return [
            'voucher_type' => 'Credit Note',
            'voucher' => $return->return_number,
            'posting_date' => $return->posting_date,

            'customer_name' => $locale === 'ar'
                ? ($return->customer?->name_ar ?? $return->customer?->name_en)
                : ($return->customer?->name_en ?? $return->customer?->name_ar),

            'receivable_account' => $locale === 'ar'
                ? ($return->salesInvoice?->receivableAccount?->name_ar ?? $return->salesInvoice?->receivableAccount?->name_en)
                : ($return->salesInvoice?->receivableAccount?->name_en ?? $return->salesInvoice?->receivableAccount?->name_ar),

            'owner' => $return->creator?->name ?? $return->creator?->email ?? '-',

            'sales_order' => $return->salesInvoice?->salesOrder?->order_number ?? '-',
            'warehouse' => $warehouseNames ?: '-',

            'sales_account' => $returnItemTotal,
            'vat' =>  (float) $return->tax_total,
            'fees' =>  (float) $return->fees_total,
            'discount' =>  (float) $return->discount_amount,
            'net_total' => (float) ($returnItemTotal + (float) $return->discount_amount),
            'grand_total' =>  (float) $return->grand_total,
            'outstanding' =>  (float) $return->grand_total,
        ];
    });
}
}