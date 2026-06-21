<?php

namespace App\Services\PurchaseInvoice;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReceipt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseInvoiceService
{
    public function createFromPurchaseReceipt(PurchaseReceipt $receipt, array $extra = []): PurchaseInvoice
    {
        return DB::transaction(function () use ($receipt, $extra) {
            $receipt->load(['items.item', 'taxes', 'fees']);

            if ($receipt->status !== 'submitted') {
                throw new RuntimeException('Purchase receipt must be submitted.');
            }

            $exists = PurchaseInvoice::where('purchase_receipt_id', $receipt->id)->first();

            if ($exists) {
                return $exists->load(['supplier','items','taxes','fees']);
            }

            $accounts = $this->resolveAccounts($receipt->company_id, $extra);
            $paidAmount = (float) ($extra['paid_amount'] ?? 0);
            $paymentMode = $extra['payment_mode'] ?? 'credit';

            if ($paymentMode !== 'credit' && round($paidAmount, 2) !== round((float) $receipt->grand_total, 2)) {
                throw new RuntimeException('Paid amount must equal grand total for paid purchase invoice.');
            }

            if ($paymentMode === 'credit') {
                $paidAmount = 0;
            }

            $invoice = PurchaseInvoice::create([
                'company_id' => $receipt->company_id,
                'purchase_receipt_id' => $receipt->id,
                'purchase_order_id' => $receipt->purchase_order_id,
                'supplier_id' => $receipt->supplier_id,

                'invoice_number' => $this->generateInvoiceNumber($receipt->company_id),
               'posting_date' => !empty($extra['posting_date'])
    ? $extra['posting_date']
    : now()->toDateString(),

'posting_time' => !empty($extra['posting_time'])
    ? $extra['posting_time']
    : now()->format('H:i:s'),

'due_date' => !empty($extra['due_date'])
    ? $extra['due_date']
    : null,

'supplier_invoice_no' => !empty($extra['supplier_invoice_no'])
    ? $extra['supplier_invoice_no']
    : null,

'supplier_invoice_date' => !empty($extra['supplier_invoice_date'])
    ? $extra['supplier_invoice_date']
    : null,
                'posting_method' => $extra['posting_method'] ?? 'default',
                'payment_mode' => $paymentMode,

                'stock_account_id' => $accounts['stock_account_id'],
                'purchase_account_id' => $accounts['purchase_account_id'],
                'supplier_payable_account_id' => $accounts['supplier_payable_account_id'],
                'cash_account_id' => $accounts['cash_account_id'],
                'bank_account_id' => $accounts['bank_account_id'],

                'total_qty' => $receipt->total_qty,
                'net_total' => $receipt->total,
                'tax_total' => $receipt->tax_total,
                'fees_total' => $receipt->fees_total,
                'discount_percentage' => $receipt->additional_discount_percentage,
                'discount_amount' => $receipt->additional_discount_amount,
                'grand_total' => $receipt->grand_total,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => max((float) $receipt->grand_total - $paidAmount, 0),

                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            foreach ($receipt->items as $item) {
                $invoice->items()->create([
                    'purchase_receipt_item_id' => $item->id,
                    'item_id' => $item->item_id,
                    'warehouse_id' => $item->accepted_warehouse_id,
                    'item_code' => $item->item->item_code,
                    'item_name_ar' => $item->item->name_ar,
                    'item_name_en' => $item->item->name_en,
                    'quantity' => $item->accepted_qty,
                    'rate' => $item->rate,
                    'amount' => $item->accepted_qty * $item->rate,
                ]);
            }

            foreach ($receipt->taxes as $tax) {
                $invoice->taxes()->create($tax->only([
                    'tax_template_id',
                    'tax_template_line_id',
                    'title',
                    'type',
                    'account_id',
                    'tax_rate',
                    'amount',
                ]));
            }

            foreach ($receipt->fees as $fee) {
                $invoice->fees()->create($fee->only([
                    'fees_template_id',
                    'title',
                    'type',
                    'account_id',
                    'fees_rate',
                    'amount',
                ]));
            }

            return $invoice->fresh()->load(['supplier','items','taxes.account','fees.account']);
        });
    }

    public function submit(PurchaseInvoice $invoice): PurchaseInvoice
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException('Only draft purchase invoices can be submitted.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->load(['items', 'taxes', 'fees']);

            $journalEntry = $this->createJournalEntry($invoice);

            $invoice->update([
                'status' => 'submitted',
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $invoice->fresh()->load(['supplier','items','taxes.account','fees.account','journalEntry']);
        });
    }
public function update(PurchaseInvoice $invoice, array $data): PurchaseInvoice
{
    if ($invoice->status !== 'draft') {
        abort(422, 'Only draft purchase invoices can be updated.');
    }

    return DB::transaction(function () use ($invoice, $data) {

        $invoice->update([
            'posting_date' => $data['posting_date'],
            'posting_time' => $data['posting_time'],
            'due_date' => $data['due_date'],
            'supplier_invoice_no' => $data['supplier_invoice_no'],
            'supplier_invoice_date' => $data['supplier_invoice_date'],
            'payment_mode' => $data['payment_mode'],
            'paid_amount' => $data['paid_amount'],
        ]);

        $invoice->items()->delete();
        $invoice->taxes()->delete();
        $invoice->fees()->delete();

        // إعادة إنشاء items
        // إعادة إنشاء taxes
        // إعادة إنشاء fees

        return $invoice->fresh([
            'items',
            'taxes',
            'fees',
        ]);
    });
}
    public function cancel(PurchaseInvoice $invoice): PurchaseInvoice
    {
        if ($invoice->status !== 'submitted') {
            throw new RuntimeException('Only submitted purchase invoices can be cancelled.');
        }

        return DB::transaction(function () use ($invoice) {
            $reverse = $this->createReverseJournalEntry($invoice);

            $invoice->update([
                'status' => 'cancelled',
                'journal_entry_id' => $reverse->id,
            ]);

            return $invoice->fresh()->load(['supplier','items','taxes.account','fees.account','journalEntry']);
        });
    }

    private function resolveAccounts(int $companyId, array $data): array
    {
        if (($data['posting_method'] ?? 'default') === 'manual') {
            return [
                'stock_account_id' => $data['stock_account_id'] ?? null,
                'purchase_account_id' => $data['purchase_account_id'] ?? null,
                'supplier_payable_account_id' => $data['supplier_payable_account_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
            ];
        }

        $settings = CompanyAccountSetting::where('company_id', $companyId)->firstOrFail();

        return [
            'stock_account_id' => $settings->default_inventory_account_id,
            'purchase_account_id' => $settings->default_direct_expense_account_id ?? $settings->default_cogs_account_id,
            'supplier_payable_account_id' => $settings->default_payable_account_id,
            'cash_account_id' => $settings->default_cash_account_id,
            'bank_account_id' => $settings->default_bank_account_id,
        ];
    }

    private function createJournalEntry(PurchaseInvoice $invoice): JournalEntry
    {
        $entry = JournalEntry::create([
            'company_id' => $invoice->company_id,
            'entry_number' => $this->generateJournalEntryNumber($invoice->company_id),
            'entry_date' => $invoice->posting_date,
            'total_debit' => 0,
            'total_credit' => 0,
            'description' => 'Purchase Invoice - ' . $invoice->invoice_number,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
        ]);

        $debit = 0;
        $credit = 0;

        $entry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $invoice->stock_account_id,
            'debit' => $invoice->net_total,
            'credit' => 0,
            'note' => 'Inventory / Purchases',
        ]);
        $debit += $invoice->net_total;

        foreach ($invoice->taxes as $tax) {
            $entry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $tax->account_id,
                'debit' => $tax->amount,
                'credit' => 0,
                'note' => 'Input Tax',
            ]);
            $debit += $tax->amount;
        }

        foreach ($invoice->fees as $fee) {
            $entry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $fee->account_id,
                'debit' => $fee->amount,
                'credit' => 0,
                'note' => 'Purchase Fees',
            ]);
            $debit += $fee->amount;
        }

        if ((float) $invoice->discount_amount > 0) {
            $entry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $invoice->purchase_account_id,
                'debit' => 0,
                'credit' => $invoice->discount_amount,
                'note' => 'Purchase Discount',
            ]);
            $credit += $invoice->discount_amount;
        }

        $creditAccount = match ($invoice->payment_mode) {
            'cash' => $invoice->cash_account_id,
            'bank', 'cheque', 'credit_card' => $invoice->bank_account_id,
            default => $invoice->supplier_payable_account_id,
        };

        $entry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $creditAccount,
            'debit' => 0,
            'credit' => $invoice->grand_total,
            'note' => 'Purchase Invoice Payment / Payable',
        ]);
        $credit += $invoice->grand_total;

        if (round($debit, 2) !== round($credit, 2)) {
            throw new RuntimeException('Purchase invoice journal entry is not balanced.');
        }

        $entry->update([
            'total_debit' => $debit,
            'total_credit' => $credit,
        ]);

        return $entry;
    }

    private function createReverseJournalEntry(PurchaseInvoice $invoice): JournalEntry
    {
        $original = $invoice->journalEntry()->with('lines')->firstOrFail();

        $entry = JournalEntry::create([
            'company_id' => $invoice->company_id,
            'entry_number' => $this->generateJournalEntryNumber($invoice->company_id),
            'entry_date' => now()->toDateString(),
            'total_debit' => $original->total_credit,
            'total_credit' => $original->total_debit,
            'description' => 'Reverse Purchase Invoice - ' . $invoice->invoice_number,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
        ]);

        foreach ($original->lines as $line) {
            $entry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $line->account_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'note' => 'Reverse: ' . $line->note,
            ]);
        }

        return $entry;
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $count = PurchaseInvoice::withTrashed()->where('company_id', $companyId)->count() + 1;
        return 'PINV-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $count = JournalEntry::where('company_id', $companyId)->count() + 1;
        return 'JV-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}