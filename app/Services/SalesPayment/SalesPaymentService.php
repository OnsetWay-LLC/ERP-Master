<?php

namespace App\Services\SalesPayment;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesPaymentService
{
    public function getAll(array $filters = [])
    {
        $query = SalesPayment::with([
            'salesInvoice',
            'customer',
            'paymentAccount',
            'receivableAccount',
        ]);

        if (!empty($filters['sales_invoice_id'])) {
            $query->where('sales_invoice_id', $filters['sales_invoice_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): SalesPayment
    {
        $companyId = Company::query()->value('id');

app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['payment_date'],
        'create'
    );
        return DB::transaction(function () use ($data) {
            $companyId = Company::query()->value('id');

            $invoice = SalesInvoice::query()
                ->where('id', $data['sales_invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status !== 'submitted') {
                throw new RuntimeException('Only submitted sales invoices can be paid.');
            }

            $this->ensureInvoicePaymentFields($invoice);

            if ((float) $invoice->outstanding_amount <= 0) {
                throw new RuntimeException('Sales invoice is already fully paid.');
            }

            if ((float) $data['paid_amount'] > (float) $invoice->outstanding_amount) {
                throw new RuntimeException('Paid amount cannot exceed outstanding amount.');
            }

           $accounts = $this->resolveAccounts($invoice, $data, $companyId);
            $payment = SalesPayment::create([
                'company_id' => $companyId,
                'sales_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'payment_number' => $this->generatePaymentNumber($companyId),
                'payment_date' => $data['payment_date'],
                'payment_time' => $data['payment_time'],
                'posting_method' => $accounts['posting_method'],
                'payment_mode' => $data['payment_mode'],
                'receivable_account_id' => $accounts['receivable_account_id'],
                'payment_account_id' => $accounts['payment_account_id'],
                'invoice_amount' => $invoice->grand_total,
                'paid_amount' => $data['paid_amount'],
                'outstanding_before' => $invoice->outstanding_amount,
                'outstanding_after' => (float) $invoice->outstanding_amount - (float) $data['paid_amount'],
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            return $payment->load([
                'salesInvoice',
                'customer',
                'paymentAccount',
                'receivableAccount',
            ]);
        });
    }

    public function submit(SalesPayment $payment): SalesPayment
    {
        if ($payment->status !== 'draft') {
            throw new RuntimeException('Only draft payments can be submitted.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $payment->company_id,
        $payment->payment_date,
        'create'
    );
        return DB::transaction(function () use ($payment) {
            $payment->load('salesInvoice');

            $invoice = SalesInvoice::query()
                ->where('id', $payment->sales_invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $payment->paid_amount > (float) $invoice->outstanding_amount) {
                throw new RuntimeException('Paid amount cannot exceed outstanding amount.');
            }

            $journalEntry = $this->createJournalEntry($payment);

            $newPaid = (float) $invoice->paid_amount + (float) $payment->paid_amount;
            $newOutstanding = (float) $invoice->grand_total - $newPaid;

            $invoice->update([
                'paid_amount' => $newPaid,
                'outstanding_amount' => max($newOutstanding, 0),
                'payment_status' => $this->paymentStatus($newOutstanding),
            ]);

            $payment->update([
                'status' => 'submitted',
                'journal_entry_id' => $journalEntry->id,
                'outstanding_after' => max($newOutstanding, 0),
            ]);

            return $payment->fresh()->load([
                'salesInvoice',
                'customer',
                'paymentAccount',
                'receivableAccount',
                'journalEntry',
            ]);
        });
    }

    public function cancel(SalesPayment $payment): SalesPayment
    {
        if ($payment->status !== 'submitted') {
            throw new RuntimeException('Only submitted payments can be cancelled.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $payment->company_id,
        $payment->payment_date,
        'update'
    );
        return DB::transaction(function () use ($payment) {
            $invoice = SalesInvoice::query()
                ->where('id', $payment->sales_invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->createReverseJournalEntry($payment);

            $newPaid = (float) $invoice->paid_amount - (float) $payment->paid_amount;
            $newOutstanding = (float) $invoice->grand_total - $newPaid;

            $invoice->update([
                'paid_amount' => max($newPaid, 0),
                'outstanding_amount' => max($newOutstanding, 0),
                'payment_status' => $this->paymentStatus($newOutstanding),
            ]);

            $payment->update([
                'status' => 'cancelled',
            ]);

            return $payment->fresh()->load([
                'salesInvoice',
                'customer',
                'paymentAccount',
                'receivableAccount',
            ]);
        });
    }

private function resolveAccounts(SalesInvoice $invoice, array $data, int $companyId): array
{
    $settings = CompanyAccountSetting::where('company_id', $companyId)->first();

    if (! $settings) {
        throw new RuntimeException('Company account settings are not configured.');
    }

    $paymentAccountId = match ($data['payment_mode']) {
        'cash' => $settings->default_cash_account_id,
        'bank' => $settings->default_bank_account_id,
        default => null,
    };

    if (! $invoice->receivable_account_id) {
        throw new RuntimeException('Sales invoice receivable account is not configured.');
    }

    if (! $paymentAccountId) {
        throw new RuntimeException('Default payment account is not configured for selected payment mode.');
    }

    return [
        'posting_method' => $invoice->posting_method,
        'receivable_account_id' => $invoice->receivable_account_id,
        'payment_account_id' => $paymentAccountId,
    ];
}

    private function createJournalEntry(SalesPayment $payment): JournalEntry
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $payment->company_id,
        $payment->payment_date,
        'create'
    );
        $journalEntry = JournalEntry::create([
            'company_id' => $payment->company_id,
            'entry_number' => $this->generateJournalEntryNumber($payment->company_id),
            'entry_date' => $payment->payment_date,
            'total_debit' => $payment->paid_amount,
            'total_credit' => $payment->paid_amount,
            'description' => 'Sales Payment - ' . $payment->payment_number,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
        ]);

        $journalEntry->lines()->create([
            'company_id' => $payment->company_id,
            'account_id' => $payment->payment_account_id,
            'debit' => $payment->paid_amount,
            'credit' => 0,
            'note' => 'Receipt from customer',
        ]);

        $journalEntry->lines()->create([
            'company_id' => $payment->company_id,
            'account_id' => $payment->receivable_account_id,
            'debit' => 0,
            'credit' => $payment->paid_amount,
            'note' => 'Reduce customer receivable',
        ]);

        return $journalEntry;
    }

    private function createReverseJournalEntry(SalesPayment $payment): JournalEntry
{
    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $payment->company_id,
            $payment->payment_date,
            'update'
        );

    $journalEntry = JournalEntry::create([
        'company_id' => $payment->company_id,
        'entry_number' => $this->generateJournalEntryNumber($payment->company_id),
        'entry_date' => $payment->payment_date,
        'total_debit' => $payment->paid_amount,
        'total_credit' => $payment->paid_amount,
        'description' => 'Reverse Sales Payment - ' . $payment->payment_number,
        'status' => 'posted',
        'created_by' => auth('api')->id(),
    ]);

    $journalEntry->lines()->create([
        'company_id' => $payment->company_id,
        'account_id' => $payment->receivable_account_id,
        'debit' => $payment->paid_amount,
        'credit' => 0,
        'note' => 'Reverse customer receivable',
    ]);

    $journalEntry->lines()->create([
        'company_id' => $payment->company_id,
        'account_id' => $payment->payment_account_id,
        'debit' => 0,
        'credit' => $payment->paid_amount,
        'note' => 'Reverse receipt account',
    ]);

    return $journalEntry;
}

    private function ensureInvoicePaymentFields(SalesInvoice $invoice): void
    {
        if ((float) $invoice->outstanding_amount <= 0 && (float) $invoice->paid_amount <= 0) {
            $invoice->update([
                'paid_amount' => 0,
                'outstanding_amount' => $invoice->grand_total,
                'payment_status' => 'unpaid',
            ]);
        }
    }

    private function paymentStatus(float $outstanding): string
    {
        if ($outstanding <= 0) {
            return 'paid';
        }

        return 'partially_paid';
    }

   private function generatePaymentNumber(int $companyId): string
{
    $year = now()->format('Y');
    $prefix = 'SPAY-' . $year . '-';

    $lastNumber = SalesPayment::where('company_id', $companyId)
        ->where('payment_number', 'like', $prefix . '%')
        ->selectRaw("
            MAX(CAST(RIGHT(payment_number, 5) AS INT)) as max_number
        ")
        ->value('max_number');

    $nextNumber = ((int) $lastNumber) + 1;

    return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}
   private function generateJournalEntryNumber(int $companyId): string
{
    $year = now()->format('Y');
    $prefix = 'JV-' . $year . '-';

    $lastNumber = JournalEntry::where('company_id', $companyId)
        ->where('entry_number', 'like', $prefix . '%')
        ->selectRaw("
            MAX(CAST(RIGHT(entry_number, 5) AS INT)) as max_number
        ")
        ->value('max_number');

    $nextNumber = ((int) $lastNumber) + 1;

    return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}
}