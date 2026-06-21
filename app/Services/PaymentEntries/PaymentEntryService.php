<?php

namespace App\Services\PaymentEntries;

use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentEntry;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class PaymentEntryService
{
    public function createFromPurchaseInvoice(array $data): PaymentEntry
    {
        return DB::transaction(function () use ($data) {
            $invoice = PurchaseInvoice::with('supplier')
                ->lockForUpdate()
                ->findOrFail($data['purchase_invoice_id']);

            if ($invoice->status !== 'submitted') {
                abort(422, 'Payment Entry can only be created from submitted Purchase Invoice.');
            }

            if ((float) $invoice->outstanding_amount <= 0) {
                abort(422, 'This Purchase Invoice is already fully paid.');
            }

            if ((float) $data['paid_amount'] > (float) $invoice->outstanding_amount) {
                abort(422, 'Paid amount cannot be greater than outstanding amount.');
            }

            $settings = CompanyAccountSetting::first();

            if (! $settings) {
                abort(422, 'Company account settings are not configured.');
            }

            $paidFromAccountId = match ($data['payment_mode']) {
                'cash' => $settings->default_cash_account_id,
                'bank' => $settings->default_bank_account_id,
            };

            $payableAccountId = $invoice->supplier_payable_account_id
                ?? $settings->default_payable_account_id;

            if (! $paidFromAccountId || ! $payableAccountId) {
                abort(422, 'Payment accounts are not configured.');
            }

            $beforeOutstanding = (float) $invoice->outstanding_amount;
            $paidAmount = (float) $data['paid_amount'];
            $afterOutstanding = round($beforeOutstanding - $paidAmount, 2);

            $paymentEntry = PaymentEntry::create([
             'company_id' => $invoice->company_id,
                'series' => $this->generateSeries(),
                'posting_date' => $data['posting_date'],
                'supplier_id' => $invoice->supplier_id,
                'payment_mode' => $data['payment_mode'],
                'paid_from_account_id' => $paidFromAccountId,
                'payable_account_id' => $payableAccountId,
                'paid_amount' => $paidAmount,
                'reference_no' => $data['reference_no'] ?? null,
                'reference_date' => $data['reference_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            $paymentEntry->references()->create([
                
                'purchase_invoice_id' => $invoice->id,
                'invoice_amount' => $invoice->grand_total,
                'outstanding_before_payment' => $beforeOutstanding,
                'allocated_amount' => $paidAmount,
                'outstanding_after_payment' => $afterOutstanding,
            ]);

            return $paymentEntry->load([
                 
                'supplier',
                'paidFromAccount',
                'payableAccount',
                'references.purchaseInvoice',
            ]);
        });
    }

    public function submit(PaymentEntry $paymentEntry): PaymentEntry
    {
        return DB::transaction(function () use ($paymentEntry) {
            $paymentEntry = PaymentEntry::lockForUpdate()
                ->with('references.purchaseInvoice')
                ->findOrFail($paymentEntry->id);

            if ($paymentEntry->status !== 'draft') {
                abort(422, 'Only draft Payment Entry can be submitted.');
            }

            $reference = $paymentEntry->references->first();

            if (! $reference) {
                abort(422, 'Payment Entry has no purchase invoice reference.');
            }

            $invoice = PurchaseInvoice::lockForUpdate()
                ->findOrFail($reference->purchase_invoice_id);

            if ($invoice->status !== 'submitted') {
                abort(422, 'Related Purchase Invoice must be submitted.');
            }

            if ((float) $paymentEntry->paid_amount > (float) $invoice->outstanding_amount) {
                abort(422, 'Paid amount cannot be greater than current outstanding amount.');
            }

            $journalEntry = $this->createJournalEntry($paymentEntry);

            $newPaidAmount = round((float) $invoice->paid_amount + (float) $paymentEntry->paid_amount, 2);
            $newOutstanding = round((float) $invoice->outstanding_amount - (float) $paymentEntry->paid_amount, 2);

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'outstanding_amount' => $newOutstanding,
                'payment_status' => $newOutstanding <= 0 ? 'paid' : 'partially_paid',
            ]);

            $reference->update([
                'outstanding_before_payment' => (float) $invoice->outstanding_amount + (float) $paymentEntry->paid_amount,
                'outstanding_after_payment' => $newOutstanding,
            ]);

            $paymentEntry->update([
                'status' => 'submitted',
                'journal_entry_id' => $journalEntry->id,
                'submitted_at' => now(),
            ]);

            return $paymentEntry->load([
               
                'supplier',
                'paidFromAccount',
                'payableAccount',
                'references.purchaseInvoice',
                'journalEntry.lines.account',
            ]);
        });
    }

    private function createJournalEntry(PaymentEntry $paymentEntry): JournalEntry
    {
        $amount = (float) $paymentEntry->paid_amount;

        $invoice = $paymentEntry->references->first()?->purchaseInvoice;

$journalEntry = JournalEntry::create([
    'company_id' => $invoice->company_id,
'entry_number' => $this->generateJournalEntryNumber($invoice->company_id),
    'entry_date' => $paymentEntry->posting_date,
    'total_debit' => $amount,
    'total_credit' => $amount,
    'status' => 'posted',
    'created_by' => auth('api')->id(),
    'posted_at' => now(),
]);

       $journalEntry->lines()->create([
    'company_id' => $journalEntry->company_id,
    'account_id' => $paymentEntry->payable_account_id,
    'debit' => $amount,
    'credit' => 0,
    'note' => 'Payment Entry: ' . $paymentEntry->series,
]);

$journalEntry->lines()->create([
    'company_id' => $journalEntry->company_id,
    'account_id' => $paymentEntry->paid_from_account_id,
    'debit' => 0,
    'credit' => $amount,
    'note' => 'Payment Entry: ' . $paymentEntry->series,
]);
        return $journalEntry;
    }

    private function generateSeries(): string
    {
        $year = now()->format('Y');

        $last = PaymentEntry::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $next = $last ? ((int) substr($last->series, -5)) + 1 : 1;

        return 'PE-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

  private function generateJournalEntryNumber(int $companyId): string
{
    $year = now()->format('Y');

    $lastNumber = JournalEntry::where('company_id', $companyId)
        ->where('entry_number', 'like', 'JV-' . $year . '-%')
        ->selectRaw("
            MAX(CAST(RIGHT(entry_number, 5) AS INT)) as max_number
        ")
        ->value('max_number');

    $next = ((int) $lastNumber) + 1;

    return 'JV-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
}
    public function update(PaymentEntry $paymentEntry, array $data): PaymentEntry
{
    return DB::transaction(function () use ($paymentEntry, $data) {
        $paymentEntry = PaymentEntry::lockForUpdate()
            ->with('references.purchaseInvoice')
            ->findOrFail($paymentEntry->id);

        if ($paymentEntry->status !== 'draft') {
            abort(422, 'Only draft Payment Entry can be edited.');
        }

        $reference = $paymentEntry->references->first();

        if (! $reference) {
            abort(422, 'Payment Entry has no purchase invoice reference.');
        }

        $invoice = PurchaseInvoice::lockForUpdate()
            ->findOrFail($reference->purchase_invoice_id);

        if ($invoice->status !== 'submitted') {
            abort(422, 'Related Purchase Invoice must be submitted.');
        }

        if ((float) $data['paid_amount'] > (float) $invoice->outstanding_amount) {
            abort(422, 'Paid amount cannot be greater than outstanding amount.');
        }

        $settings = CompanyAccountSetting::first();

        if (! $settings) {
            abort(422, 'Company account settings are not configured.');
        }

        $paidFromAccountId = match ($data['payment_mode']) {
            'cash' => $settings->default_cash_account_id,
            'bank' => $settings->default_bank_account_id,
        };

        if (! $paidFromAccountId) {
            abort(422, 'Payment account is not configured.');
        }

        $beforeOutstanding = (float) $invoice->outstanding_amount;
        $paidAmount = (float) $data['paid_amount'];
        $afterOutstanding = round($beforeOutstanding - $paidAmount, 2);

        $paymentEntry->update([
            'posting_date' => $data['posting_date'],
            'payment_mode' => $data['payment_mode'],
            'paid_from_account_id' => $paidFromAccountId,
            'paid_amount' => $paidAmount,
            'reference_no' => $data['reference_no'] ?? null,
            'reference_date' => $data['reference_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ]);

        $reference->update([
            'invoice_amount' => $invoice->grand_total,
            'outstanding_before_payment' => $beforeOutstanding,
            'allocated_amount' => $paidAmount,
            'outstanding_after_payment' => $afterOutstanding,
        ]);

        return $paymentEntry->load([
            'supplier',
            'paidFromAccount',
            'payableAccount',
            'references.purchaseInvoice',
        ]);
    });
}

public function cancel(PaymentEntry $paymentEntry): PaymentEntry
{
    return DB::transaction(function () use ($paymentEntry) {
        $paymentEntry = PaymentEntry::lockForUpdate()
            ->with('references.purchaseInvoice', 'journalEntry')
            ->findOrFail($paymentEntry->id);

        if ($paymentEntry->status !== 'submitted') {
            abort(422, 'Only submitted Payment Entry can be cancelled.');
        }

        $reference = $paymentEntry->references->first();

        if (! $reference) {
            abort(422, 'Payment Entry has no purchase invoice reference.');
        }

        $invoice = PurchaseInvoice::lockForUpdate()
            ->findOrFail($reference->purchase_invoice_id);

        $paidAmount = (float) $paymentEntry->paid_amount;

        $newPaidAmount = round((float) $invoice->paid_amount - $paidAmount, 2);
        $newOutstanding = round((float) $invoice->outstanding_amount + $paidAmount, 2);

        if ($newPaidAmount < 0) {
            $newPaidAmount = 0;
        }

        $invoice->update([
            'company_id' => $invoice->company_id,
            'paid_amount' => $newPaidAmount,
            'outstanding_amount' => $newOutstanding,
            'payment_status' => $newPaidAmount <= 0
                ? 'unpaid'
                : 'partially_paid',
        ]);

        $reverseJournalEntry = $this->createReverseJournalEntry($paymentEntry);

        $paymentEntry->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $paymentEntry->load([
            'supplier',
            'paidFromAccount',
            'payableAccount',
            'references.purchaseInvoice',
            'journalEntry.lines.account',
        ]);
    });
}

private function createReverseJournalEntry(PaymentEntry $paymentEntry): JournalEntry
{
    $amount = (float) $paymentEntry->paid_amount;

   $invoice = $paymentEntry->references->first()?->purchaseInvoice;

$journalEntry = JournalEntry::create([
    'company_id' => $invoice->company_id,
'entry_number' => $this->generateJournalEntryNumber($invoice->company_id),
    'entry_date' => now()->toDateString(),
    'total_debit' => $amount,
    'total_credit' => $amount,
    'status' => 'posted',
    'created_by' => auth('api')->id(),
    'posted_at' => now(),
]);

   $journalEntry->lines()->create([
    'company_id' => $journalEntry->company_id,
    'account_id' => $paymentEntry->paid_from_account_id,
    'debit' => $amount,
    'credit' => 0,
    'note' => 'Reverse Payment Entry: ' . $paymentEntry->series,
]);

$journalEntry->lines()->create([
    'company_id' => $journalEntry->company_id,
    'account_id' => $paymentEntry->payable_account_id,
    'debit' => 0,
    'credit' => $amount,
    'note' => 'Reverse Payment Entry: ' . $paymentEntry->series,
]);
    return $journalEntry;
}
public function delete(PaymentEntry $paymentEntry): void
{
    DB::transaction(function () use ($paymentEntry) {

        $paymentEntry = PaymentEntry::lockForUpdate()
            ->with('references')
            ->findOrFail($paymentEntry->id);

        if ($paymentEntry->status !== 'draft') {
            abort(422, 'Only draft Payment Entry can be deleted.');
        }

        $paymentEntry->references()->delete();

        $paymentEntry->delete();
    });
}
}