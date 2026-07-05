<?php

namespace App\Services\Reports;

use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\SalesReturn;
use Illuminate\Support\Collection;

class SalesPaymentSummaryReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $ownerId = $filters['owner_id'] ?? null;

        $invoices = SalesInvoice::query()
            ->with(['creator'])
            ->where('status', 'submitted')
            ->whereBetween('posting_date', [$fromDate, $toDate])
            ->when($ownerId, function ($q) use ($ownerId) {
                $q->where('created_by', $ownerId);
            })
            ->get();

        $rows = collect();

        foreach ($invoices as $invoice) {
            $paymentModes = $this->getPaymentModes($invoice->id);

            if ($paymentModes->isEmpty()) {
                $paymentModes = collect([
                    app()->getLocale() === 'ar' ? 'لم يتم الدفع بعد' : 'Not Paid Yet',
                ]);
            }

            foreach ($paymentModes as $paymentMode) {
                $key = $invoice->posting_date . '|' . $invoice->created_by . '|' . $paymentMode;

                if (! $rows->has($key)) {
                    $rows->put($key, [
                        'date' => $invoice->posting_date,
                        'owner' => $invoice->creator?->name
                            ?? $invoice->creator?->email
                            ?? '-',
                        'payment_mode' => $paymentMode,
                        'sales_return' => 0,
                        'tax' => 0,
                        'payment' => 0,
                    ]);
                }

                $row = $rows->get($key);

                $returnNet = $this->getReturnNetTotal($invoice->id);
                $returnTax = $this->getReturnTaxTotal($invoice->id);

                $row['sales_return'] +=
                    (float) $invoice->net_total
                    + $returnNet;

                $row['tax'] +=
                    (float) $invoice->tax_total
                    - $returnTax;

                $row['payment'] += $this->getPaymentAmountByMode($invoice->id, $paymentMode);

                $rows->put($key, $row);
            }
        }

        $finalRows = $rows->values();

        return [
            'rows' => $finalRows->toArray(),
            'totals' => [
                'sales_return' => round($finalRows->sum('sales_return'), 2),
                'tax' => round($finalRows->sum('tax'), 2),
                'payment' => round($finalRows->sum('payment'), 2),
            ],
        ];
    }

    private function getPaymentModes(int $invoiceId): Collection
    {
        return SalesPayment::query()
            ->where('sales_invoice_id', $invoiceId)
            ->where('status', 'submitted')
            ->pluck('payment_mode')
            ->unique()
            ->values();
    }

    private function getPaymentAmountByMode(int $invoiceId, string $paymentMode): float
    {
        if (
            $paymentMode === 'Not Paid Yet'
            || $paymentMode === 'لم يتم الدفع بعد'
        ) {
            return 0;
        }

        return (float) SalesPayment::query()
            ->where('sales_invoice_id', $invoiceId)
            ->where('status', 'submitted')
            ->where('payment_mode', $paymentMode)
            ->sum('paid_amount');
    }

    private function getReturnNetTotal(int $invoiceId): float
    {
        return (float) SalesReturn::query()
            ->where('sales_invoice_id', $invoiceId)
            ->where('status', 'submitted')
            ->sum('net_total');
    }

    private function getReturnTaxTotal(int $invoiceId): float
    {
        return (float) SalesReturn::query()
            ->where('sales_invoice_id', $invoiceId)
            ->where('status', 'submitted')
            ->sum('tax_total');
    }
}