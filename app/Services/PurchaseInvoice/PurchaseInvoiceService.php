<?php

namespace App\Services\PurchaseInvoice;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReceipt;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseInvoiceService
{
    public function createFromPurchaseReceipt(PurchaseReceipt $receipt, array $extra = []): PurchaseInvoice
    {

    app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $receipt->company_id,
        !empty($extra['posting_date'])
            ? $extra['posting_date']
            : now()->toDateString(),
        'create'
    );
        return DB::transaction(function () use ($receipt, $extra) {
            $receipt->load(['items.item', 'taxes', 'fees']);

            if ($receipt->status !== 'submitted') {
                throw new RuntimeException('Purchase receipt must be submitted.');
            }

            $exists = PurchaseInvoice::where('purchase_receipt_id', $receipt->id)->first();

           if ($exists) {
    if ($exists->status !== 'draft') {
        throw new RuntimeException('Purchase invoice already exists for this receipt.');
    }

   $paymentMode = $extra['payment_mode'] ?? $exists->payment_mode;
$paidAmount = 0;

    $exists->update([
        'posting_date' => !empty($extra['posting_date']) ? $extra['posting_date'] : $exists->posting_date,
        'posting_time' => !empty($extra['posting_time']) ? $extra['posting_time'] : $exists->posting_time,
        'due_date' => !empty($extra['due_date']) ? $extra['due_date'] : $exists->due_date,
        'supplier_invoice_no' => !empty($extra['supplier_invoice_no']) ? $extra['supplier_invoice_no'] : $exists->supplier_invoice_no,
        'supplier_invoice_date' => !empty($extra['supplier_invoice_date']) ? $extra['supplier_invoice_date'] : $exists->supplier_invoice_date,
        'posting_method' => $extra['posting_method'] ?? $exists->posting_method,
        'payment_mode' => $paymentMode,
        'paid_amount' => $paidAmount,
        'outstanding_amount' => max((float) $exists->grand_total - $paidAmount, 0),
    ]);

    return $exists->fresh()->load(['supplier','items','taxes.account','fees.account']);
}

            $accounts = $this->resolveAccounts($receipt->company_id, $extra);
           $paymentMode = $extra['payment_mode'] ?? 'credit';
$paidAmount = 0;

            
           

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
                'payment_mode' => 'credit',

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
                'paid_amount' => 0,
                'outstanding_amount' => (float) $receipt->grand_total,

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
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $invoice->company_id,
        $invoice->posting_date,
        'create'
    );
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
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $invoice->company_id,
        $data['posting_date'] ?? $invoice->posting_date,
        'update'
    );
    return DB::transaction(function () use ($invoice, $data) {

        $paymentMode = $data['payment_mode'] ?? $invoice->payment_mode;

        $invoice->update([
            'posting_date' => !empty($data['posting_date'])
                ? $data['posting_date']
                : $invoice->posting_date,

            'posting_time' => !empty($data['posting_time'])
                ? $data['posting_time']
                : $invoice->posting_time,

            'due_date' => array_key_exists('due_date', $data)
                ? $data['due_date']
                : $invoice->due_date,

            'supplier_invoice_no' => array_key_exists('supplier_invoice_no', $data)
                ? $data['supplier_invoice_no']
                : $invoice->supplier_invoice_no,

            'supplier_invoice_date' => array_key_exists('supplier_invoice_date', $data)
                ? $data['supplier_invoice_date']
                : $invoice->supplier_invoice_date,

            'posting_method' => $data['posting_method'] ?? $invoice->posting_method,
            'payment_mode' => 'credit',

            // الدفع لا يتم من شاشة الفاتورة
            // يتم لاحقًا من Payment Entry
            'paid_amount' => 0,
            'outstanding_amount' => (float) $invoice->grand_total,
        ]);

        return $invoice->fresh([
            'supplier',
            'items',
            'taxes.account',
            'fees.account',
            'journalEntry',
        ]);
    });
}
    public function cancel(PurchaseInvoice $invoice): PurchaseInvoice
    {
        if ($invoice->status !== 'submitted') {
            throw new RuntimeException('Only submitted purchase invoices can be cancelled.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $invoice->company_id,
        $invoice->posting_date,
        'update'
    );
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
            'stock_account_id' => $this->resolveManualAccount(
                $companyId,
                $data['stock_account_id'] ?? null,
                ['stock'],
                'Stock Account'
            ),

            'purchase_account_id' => $this->resolveManualAccount(
                $companyId,
                $data['purchase_account_id'] ?? null,
                ['direct_expense', 'indirect_expense', 'cogs'],
                'Purchase Account'
            ),

            'supplier_payable_account_id' => $this->resolveManualAccount(
                $companyId,
                $data['supplier_payable_account_id'] ?? null,
                ['payable'],
                'Supplier Payable Account'
            ),

            'cash_account_id' => $this->resolveManualAccount(
                $companyId,
                $data['cash_account_id'] ?? null,
                ['cash'],
                'Cash Account',
                false
            ),

            'bank_account_id' => $this->resolveManualAccount(
                $companyId,
                $data['bank_account_id'] ?? null,
                ['bank'],
                'Bank Account',
                false
            ),
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
private function resolveManualAccount(
    int $companyId,
    ?int $accountId,
    array $allowedTypes,
    string $label,
    bool $required = true
): ?int {
    if (! $accountId) {
        if ($required) {
            throw new RuntimeException("$label is required.");
        }

        return null;
    }

    $account = ChartOfAccount::query()
        ->where('company_id', $companyId)
        ->where('id', $accountId)
        ->where('account_level', 'child')
        ->where('is_active', true)
        ->whereNull('deleted_at')
        ->first();

    if (! $account) {
        throw new RuntimeException("$label must be an active child account.");
    }

    if (! in_array($account->account_type, $allowedTypes, true)) {
        throw new RuntimeException("$label has invalid account type.");
    }

    return $account->id;
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

        $entry->lines()->create([
    'company_id' => $invoice->company_id,
    'account_id' => $invoice->supplier_payable_account_id,
    'debit' => 0,
    'credit' => $invoice->grand_total,
    'note' => 'Supplier Payable',
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
    public function purchaseInvoiceAccounts()
{
    $companyId = 1;

    return response()->json([
        'stock_accounts' => ChartOfAccount::where('company_id', $companyId)
            ->where('account_type', 'stock')
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->get(),

        'purchase_accounts' => ChartOfAccount::where('company_id', $companyId)
            ->whereIn('account_type', ['direct_expense', 'indirect_expense', 'cogs'])
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->get(),

        'payable_accounts' => ChartOfAccount::where('company_id', $companyId)
            ->where('account_type', 'payable')
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->get(),
    ]);
}
}