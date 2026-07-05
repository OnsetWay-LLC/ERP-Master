<?php

namespace App\Services\Reports;

use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;

class ItemWiseSalesRegisterReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $customerId = $filters['customer_id'] ?? null;
        $itemId = $filters['item_id'] ?? null;

        $query = SalesInvoiceItem::query()
            ->with([
                'invoice.customer',
                'invoice.receivableAccount',
                'invoice.salesAccount',
                'invoice.salesOrder',
                'invoice.items',
                'item.itemGroup',
            ])
            ->whereHas('invoice', function ($q) use ($fromDate, $toDate, $customerId) {
                $q->where('status', 'submitted')
                    ->whereBetween('posting_date', [$fromDate, $toDate]);

                if ($customerId) {
                    $q->where('customer_id', $customerId);
                }
            });

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $rows = $query
            ->orderBy('sales_invoice_id')
            ->orderBy('id')
            ->get()
            ->map(function ($line) {
                $invoice = $line->invoice;
                $locale = app()->getLocale();

                $taxRate = $this->getInvoiceTaxRate((int) $invoice->id);

                $amount = (float) $line->amount;

                $invoiceItemsTotal = (float) $invoice->items->sum('amount');
                $invoiceDiscount = (float) $invoice->discount_amount;

                $itemDiscount = $invoiceItemsTotal > 0
                    ? ($amount / $invoiceItemsTotal) * $invoiceDiscount
                    : 0;

                $netTotal = $amount - $itemDiscount;

                $vatAmount = $netTotal * ($taxRate / 100);

                $total = $netTotal + $vatAmount;

                return [
                    'item_code' => $line->item_code,
                    'item_name' => $locale === 'ar'
                        ? ($line->item_name_ar ?? $line->item_name_en)
                        : ($line->item_name_en ?? $line->item_name_ar),

                    'item_group' => $locale === 'ar'
                        ? ($line->item?->itemGroup?->name_ar ?? $line->item?->itemGroup?->name_en)
                        : ($line->item?->itemGroup?->name_en ?? $line->item?->itemGroup?->name_ar),

                    'invoice' => $invoice?->invoice_number,
                    'posting_date' => $invoice?->posting_date,

                    'customer_name' => $locale === 'ar'
                        ? ($invoice?->customer?->name_ar ?? $invoice?->customer?->name_en)
                        : ($invoice?->customer?->name_en ?? $invoice?->customer?->name_ar),

                    'receivable_account' => $locale === 'ar'
                        ? ($invoice?->receivableAccount?->name_ar ?? $invoice?->receivableAccount?->name_en)
                        : ($invoice?->receivableAccount?->name_en ?? $invoice?->receivableAccount?->name_ar),

                    'mode_of_payment' => $this->getModeOfPayment((int) $invoice->id),

                    'sales_order_no' => $invoice?->salesOrder?->order_number ?? '-',

                    'income_account' => $locale === 'ar'
                        ? ($invoice?->salesAccount?->name_ar ?? $invoice?->salesAccount?->name_en)
                        : ($invoice?->salesAccount?->name_en ?? $invoice?->salesAccount?->name_ar),

                    'stock_qty' => round((float) $line->quantity, 2),
                    'rate' => round((float) $line->rate, 2),

                    'amount' => round($amount, 2),
                    'discount_amount' => round($itemDiscount, 2),
                    'net_total' => round($netTotal, 2),

                    'tax_rate' => round($taxRate, 2),
                    'vat_amount' => round($vatAmount, 2),
                    'total' => round($total, 2),
                ];
            })
            ->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'stock_qty' => round($rows->sum('stock_qty'), 2),
                'rate' => round($rows->sum('rate'), 2),

                'amount' => round($rows->sum('amount'), 2),
                'discount_amount' => round($rows->sum('discount_amount'), 2),
                'net_total' => round($rows->sum('net_total'), 2),

                'tax_rate' => round($rows->sum('tax_rate'), 2),
                'vat_amount' => round($rows->sum('vat_amount'), 2),
                'total' => round($rows->sum('total'), 2),
            ],
        ];
    }

    private function getInvoiceTaxRate(int $invoiceId): float
    {
        $taxAmount = (float) DB::table('sales_invoice_taxes')
            ->where('sales_invoice_id', $invoiceId)
            ->sum('amount');

        $netTotal = (float) DB::table('sales_invoices')
            ->where('id', $invoiceId)
            ->value('net_total');

        if ($netTotal <= 0 || $taxAmount <= 0) {
            return 0;
        }

        return round(($taxAmount / $netTotal) * 100, 2);
    }

    private function getModeOfPayment(int $invoiceId): string
    {
        $paymentModes = DB::table('sales_payments')
            ->where('sales_invoice_id', $invoiceId)
            ->where('status', 'submitted')
            ->pluck('payment_mode')
            ->unique()
            ->values()
            ->toArray();

        if (empty($paymentModes)) {
            return app()->getLocale() === 'ar' ? 'لم يتم الدفع بعد' : 'Not Paid Yet';
        }

        return implode(', ', $paymentModes);
    }
}