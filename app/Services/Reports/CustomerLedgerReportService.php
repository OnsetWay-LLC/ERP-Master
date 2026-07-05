<?php

namespace App\Services\Reports;

use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class CustomerLedgerReportService
{
    public function generate(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        $customers = Customer::query()
            ->whereNull('deleted_at')
            ->get();

        return $customers->map(function ($customer) use ($fromDate, $toDate) {

            $openingBalance = (float) ($customer->opening_balance ?? 0);

            $invoiceAmount = (float) SalesInvoice::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'submitted')
                ->whereBetween('posting_date', [$fromDate, $toDate])
                ->sum('grand_total');

            $paidAmount = (float) DB::table('sales_payments')
                ->where('customer_id', $customer->id)
                ->where('status', 'submitted')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('paid_amount');

            $creditNote = (float) DB::table('sales_returns')
                ->where('customer_id', $customer->id)
                ->where('status', 'submitted')
                ->whereBetween('posting_date', [$fromDate, $toDate])
                ->sum('grand_total');

            $closingBalance =
                $openingBalance
                + $invoiceAmount
                - $paidAmount
                + $creditNote;

            return [
                'customer_id' => $customer->id,
                'customer' => app()->getLocale() === 'ar'
    ? $customer->name_ar
    : $customer->name_en,
                'opening_balance' => round($openingBalance, 2),
                'invoice_amount' => round($invoiceAmount, 2),
                'paid_amount' => round($paidAmount, 2),
                'credit_note' => round($creditNote, 2),
                'closing_balance' => round(abs($closingBalance), 2),
                'dr_cr' => $this->getDrCr($closingBalance),
            ];
        })->values()->toArray();
    }

    private function getDrCr(float $balance): string
    {
        if ($balance > 0) {
            return 'Dr';
        }

        if ($balance < 0) {
            return 'Cr';
        }

        return '-';
    }
}