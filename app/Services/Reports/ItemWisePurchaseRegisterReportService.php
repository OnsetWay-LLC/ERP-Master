<?php

namespace App\Services\Reports;

use App\Models\PurchaseInvoiceItem;

class ItemWisePurchaseRegisterReportService
{
    public function generate(array $filters): array
    {
        $locale = app()->getLocale();

        $query = PurchaseInvoiceItem::query()
            ->with([
                'item.itemGroup',
                'invoice.supplier',
                'invoice.purchaseOrder',
                'invoice.purchaseReceipt',
                'invoice.paymentEntries',
                'invoice.supplierPayableAccount',
                'invoice.taxes.accountHead',
                'invoice.purchaseAccount',
            ])
            ->whereHas('invoice', function ($q) use ($filters) {
                $q->where('status', 'submitted')
                    ->whereBetween('posting_date', [
                        $filters['from_date'],
                        $filters['to_date'],
                    ]);

                if (! empty($filters['supplier_id'])) {
                    $q->where('supplier_id', $filters['supplier_id']);
                }
            });

        if (! empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        $rows = $query->orderBy('id')->get()->map(function ($row) use ($locale) {
            $invoice = $row->invoice;

            $amount = round((float) $row->quantity * (float) $row->rate, 2);

            $taxRate = (float) ($invoice->taxes->first()?->tax_rate ?? 0);

            $vatAmount = round($amount * ($taxRate / 100), 2);

            $itemName = $locale === 'ar'
                ? ($row->item_name_ar ?? $row->item_name_en)
                : ($row->item_name_en ?? $row->item_name_ar);

            $supplierName = $locale === 'ar'
                ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
                : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar);

            $payableAccount = $locale === 'ar'
                ? ($invoice->supplierPayableAccount?->name_ar ?? $invoice->supplierPayableAccount?->name_en)
                : ($invoice->supplierPayableAccount?->name_en ?? $invoice->supplierPayableAccount?->name_ar);

            return [
                'item_code' => $row->item_code,
                'item_name' => $itemName,
                'item_group' => $locale === 'ar'
                    ? ($row->item?->itemGroup?->name_ar ?? $row->item?->itemGroup?->name_en)
                    : ($row->item?->itemGroup?->name_en ?? $row->item?->itemGroup?->name_ar),

                'invoice' => $invoice->invoice_number,
                'posting_date' => $invoice->posting_date,
                'supplier_name' => $supplierName,
                'payable_account' => $payableAccount,

                'mode_of_payment' => $invoice->paymentEntries->first()?->payment_mode
                    ?? ($locale === 'ar' ? 'غير مدفوع بعد' : 'Not Paid Yet'),

                'purchase_order_no' => $invoice->purchaseOrder?->series ?? '-',
                'purchase_receipt_no' => $invoice->purchaseReceipt?->series ?? '-',

                'expenses_account' => $locale === 'ar'
    ? ($invoice->purchaseAccount?->name_ar ?? $invoice->purchaseAccount?->name_en)
    : ($invoice->purchaseAccount?->name_en ?? $invoice->purchaseAccount?->name_ar),

                'stock_qty' => (float) $row->quantity,
                'rate' => (float) $row->rate,
                'amount' => $amount,
                'tax_rate' => $taxRate,
                'vat_amount' => $vatAmount,
                'total' => round($amount + $vatAmount, 2),
            ];
        })->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'stock_qty' => round($rows->sum('stock_qty'), 2),
                'rate' => round($rows->sum('rate'), 2),
                'amount' => round($rows->sum('amount'), 2),
                'tax_rate' => round($rows->sum('tax_rate'), 2),
                'vat_amount' => round($rows->sum('vat_amount'), 2),
                'total' => round($rows->sum('total'), 2),
            ],
        ];
    }
}