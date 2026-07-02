<?php

namespace App\Services\PurchaseInvoice;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReceipt;
use App\Models\ChartOfAccount;
use App\Models\AssetItem;
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
$itemTotal = round((float) $receipt->items->sum(function ($item) {
    return (float) $item->accepted_qty * (float) $item->rate;
}), 2);

$discountAmount = round((float) $receipt->additional_discount_amount, 2);

$netTotal = $discountAmount > 0
    ? round($itemTotal - $discountAmount, 2)
    : $itemTotal;

$taxTotal = round((float) $receipt->tax_total, 2);
$feesTotal = round((float) $receipt->fees_total, 2);

$grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);
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
        'net_total' => $netTotal,
'tax_total' => $taxTotal,
'fees_total' => $feesTotal,
'discount_amount' => $discountAmount,
'grand_total' => $grandTotal,
'paid_amount' => 0,
'outstanding_amount' => $grandTotal,
    ]);

    return $exists->fresh()->load(['supplier','items','taxes.account','fees.account']);
}

            $accounts = $this->resolveAccounts($receipt->company_id, $extra);
           $paymentMode = $extra['payment_mode'] ?? 'credit';
$paidAmount = 0;

            $itemTotal = round((float) $receipt->items->sum(function ($item) {
    return (float) $item->accepted_qty * (float) $item->rate;
}), 2);

$discountAmount = round((float) $receipt->additional_discount_amount, 2);

$netTotal = $discountAmount > 0
    ? round($itemTotal - $discountAmount, 2)
    : $itemTotal;

$taxTotal = round((float) $receipt->tax_total, 2);
$feesTotal = round((float) $receipt->fees_total, 2);

$grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);
           

            $invoice = PurchaseInvoice::create([
                'company_id' => $receipt->company_id,
                'purchase_receipt_id' => $receipt->id,
                'purchase_order_id' => $receipt->purchase_order_id,
                'invoice_type' => 'from_receipt',
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
                'net_total' => $netTotal,
'tax_total' => $taxTotal,
'fees_total' => $feesTotal,
'discount_amount' => $discountAmount,
'grand_total' => $grandTotal,
'outstanding_amount' => $grandTotal,

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
  public function createManualInventory(array $data, int $companyId): PurchaseInvoice
{
    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $companyId,
            $data['posting_date'] ?? now()->toDateString(),
            'create'
        );

    return DB::transaction(function () use ($data, $companyId) {
        $accounts = $this->resolveAccounts($companyId, [
            'posting_method' => $data['posting_method'] ?? 'default',
            'purchase_account_id' => $data['purchase_account_id'] ?? null,
            'supplier_payable_account_id' => $data['supplier_payable_account_id'] ?? null,
        ]);

        $itemTotal = 0;
        $totalQty = 0;

        foreach ($data['items'] as $row) {
            $qty = (float) $row['quantity'];
            $rate = (float) $row['rate'];

            $itemTotal += $qty * $rate;
            $totalQty += $qty;
        }

        $discountAmount = 0;

        if (!empty($data['additional_discount_percentage'])) {
            $discountAmount = round(
                $itemTotal * ((float) $data['additional_discount_percentage'] / 100),
                2
            );
        }

        if (!empty($data['additional_discount_amount'])) {
            $discountAmount = round((float) $data['additional_discount_amount'], 2);
        }

        $netTotal = round($itemTotal - $discountAmount, 2);

        $taxRows = [];
        $taxTotal = 0;

        foreach (($data['tax_template_ids'] ?? []) as $taxTemplateId) {
            $template = \App\Models\TaxTemplate::with('lines')
                ->where('company_id', $companyId)
                ->findOrFail($taxTemplateId);

            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? round($netTotal * ((float) $line->tax_rate / 100), 2)
                    : round((float) ($line->amount ?? 0), 2);

                $taxRows[] = [
                    'tax_template_id' => $template->id,
                    'tax_template_line_id' => $line->id,
                    'title' => $line->title,
                    'type' => $line->type,
                    'account_id' => $line->account_id,
                    'tax_rate' => $line->tax_rate,
                    'amount' => $amount,
                ];

                $taxTotal += $amount;
            }
        }

        $feeRows = [];
        $feesTotal = 0;

        foreach (($data['fees_template_ids'] ?? []) as $feesTemplateId) {
            $template = \App\Models\FeesTemplate::where('company_id', $companyId)
                ->findOrFail($feesTemplateId);

            $amount = $template->type === 'percentage'
                ? round($netTotal * ((float) $template->fees_rate / 100), 2)
                : round((float) $template->amount, 2);

            $feeRows[] = [
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ];

            $feesTotal += $amount;
        }

        $grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);

        $invoice = PurchaseInvoice::create([
            'company_id' => $companyId,
            'purchase_receipt_id' => null,
            'purchase_order_id' => null,
            'invoice_type' => 'manual_inventory',
            'supplier_id' => $data['supplier_id'],

            'invoice_number' => $this->generateInvoiceNumber($companyId),
            'posting_date' => $data['posting_date'] ?? now()->toDateString(),
            'posting_time' => $data['posting_time'] ?? now()->format('H:i:s'),
            'due_date' => $data['due_date'] ?? null,

            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
            'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,

            'posting_method' => $data['posting_method'] ?? 'default',
            'payment_mode' => 'credit',

            'stock_account_id' => null,
            'purchase_account_id' => $accounts['purchase_account_id'],
            'supplier_payable_account_id' => $accounts['supplier_payable_account_id'],
            'cash_account_id' => $accounts['cash_account_id'],
            'bank_account_id' => $accounts['bank_account_id'],

            'total_qty' => $totalQty,
            'net_total' => $netTotal,
            'tax_total' => round($taxTotal, 2),
            'fees_total' => round($feesTotal, 2),
            'discount_percentage' => $data['additional_discount_percentage'] ?? 0,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'paid_amount' => 0,
            'outstanding_amount' => $grandTotal,

            'status' => 'draft',
            'created_by' => auth('api')->id(),
        ]);

        foreach ($data['items'] as $row) {
            $item = \App\Models\Item::where('company_id', $companyId)
                ->where('id', $row['item_id'])
                ->firstOrFail();

            $qty = (float) $row['quantity'];
            $rate = (float) $row['rate'];

            $invoice->items()->create([
                'purchase_receipt_item_id' => null,
                'item_id' => $item->id,
                'asset_item_id' => null,
                'warehouse_id' => $row['warehouse_id'] ?? null,
                'item_code' => $item->item_code,
                'item_name_ar' => $item->name_ar,
                'item_name_en' => $item->name_en,
                'quantity' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }

        foreach ($taxRows as $row) {
            $invoice->taxes()->create($row);
        }

        foreach ($feeRows as $row) {
            $invoice->fees()->create($row);
        }

        return $invoice->fresh()->load([
            'supplier',
            'items.item',
            'taxes.account',
            'fees.account',
        ]);
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
public function createManual(array $data, int $companyId): PurchaseInvoice
{
    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $companyId,
            $data['posting_date'] ?? now()->toDateString(),
            'create'
        );

    return DB::transaction(function () use ($data, $companyId) {
        $accounts = $this->resolveAccounts($companyId, $data);

        $itemTotal = 0;
        $totalQty = 0;
        $fixedAssetAccountId = null;

        foreach ($data['items'] as $row) {
            $assetItem = AssetItem::with('assetCategory')
                ->where('company_id', $companyId)
                ->where('id', $row['asset_item_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $categoryFixedAssetAccountId = $assetItem->assetCategory?->fixed_asset_account_id;

            if (! $categoryFixedAssetAccountId) {
                throw new RuntimeException('Fixed Asset Account is required on Asset Category.');
            }

            if ($fixedAssetAccountId === null) {
                $fixedAssetAccountId = (int) $categoryFixedAssetAccountId;
            }

            if ((int) $fixedAssetAccountId !== (int) $categoryFixedAssetAccountId) {
                throw new RuntimeException('All asset items must belong to categories with the same Fixed Asset Account.');
            }

            $qty = (float) $row['quantity'];
            $rate = (float) $row['rate'];

            $itemTotal += $qty * $rate;
            $totalQty += $qty;
        }

        $discountAmount = 0;

        if (! empty($data['additional_discount_percentage'])) {
            $discountAmount = round(
                $itemTotal * ((float) $data['additional_discount_percentage'] / 100),
                2
            );
        }

        if (! empty($data['additional_discount_amount'])) {
            $discountAmount = round((float) $data['additional_discount_amount'], 2);
        }

        $netTotal = round($itemTotal - $discountAmount, 2);

        $taxRows = [];
        $taxTotal = 0;

        foreach (($data['tax_template_ids'] ?? []) as $taxTemplateId) {
            $template = \App\Models\TaxTemplate::with('lines')
                ->where('company_id', $companyId)
                ->findOrFail($taxTemplateId);

            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? round($netTotal * ((float) $line->tax_rate / 100), 2)
                    : round((float) ($line->amount ?? 0), 2);

                $taxRows[] = [
                    'tax_template_id' => $template->id,
                    'tax_template_line_id' => $line->id,
                    'title' => $line->title,
                    'type' => $line->type,
                    'account_id' => $line->account_id,
                    'tax_rate' => $line->tax_rate,
                    'amount' => $amount,
                ];

                $taxTotal += $amount;
            }
        }

        $feeRows = [];
        $feesTotal = 0;

        foreach (($data['fees_template_ids'] ?? []) as $feesTemplateId) {
            $template = \App\Models\FeesTemplate::query()
                ->where('company_id', $companyId)
                ->findOrFail($feesTemplateId);

            $amount = $template->type === 'percentage'
                ? round($netTotal * ((float) $template->fees_rate / 100), 2)
                : round((float) $template->amount, 2);

            $feeRows[] = [
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ];

            $feesTotal += $amount;
        }

        $grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);

        $invoice = PurchaseInvoice::create([
            'company_id' => $companyId,
            'purchase_receipt_id' => null,
            'purchase_order_id' => null,
            'invoice_type' => 'manual_asset',
            'supplier_id' => $data['supplier_id'],

            'invoice_number' => $this->generateInvoiceNumber($companyId),
            'posting_date' => $data['posting_date'] ?? now()->toDateString(),
            'posting_time' => $data['posting_time'] ?? now()->format('H:i:s'),
            'due_date' => $data['due_date'] ?? null,

            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
            'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,

            'posting_method' => $data['posting_method'] ?? 'manual',
            'payment_mode' => 'credit',

            'stock_account_id' => null,
            'purchase_account_id' => $fixedAssetAccountId,
            'supplier_payable_account_id' => $accounts['supplier_payable_account_id'],
            'cash_account_id' => $accounts['cash_account_id'],
            'bank_account_id' => $accounts['bank_account_id'],

            'total_qty' => $totalQty,
            'net_total' => $netTotal,
            'tax_total' => round($taxTotal, 2),
            'fees_total' => round($feesTotal, 2),
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'paid_amount' => 0,
            'outstanding_amount' => $grandTotal,

            'status' => 'draft',
            'created_by' => auth('api')->id(),
        ]);

        foreach ($data['items'] as $row) {
            $assetItem = AssetItem::where('company_id', $companyId)
                ->where('id', $row['asset_item_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $qty = (float) $row['quantity'];
            $rate = (float) $row['rate'];

            $invoice->items()->create([
                'purchase_receipt_item_id' => null,
                'item_id' => null,
                'asset_item_id' => $assetItem->id,
                'warehouse_id' => null,
                'item_code' => $assetItem->item_code,
                'item_name_ar' => $assetItem->item_name,
                'item_name_en' => $assetItem->item_name,
                'quantity' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }

        foreach ($taxRows as $row) {
            $invoice->taxes()->create($row);
        }

        foreach ($feeRows as $row) {
            $invoice->fees()->create($row);
        }

        return $invoice->fresh()->load([
            'supplier',
            'items.assetItem.assetCategory',
            'taxes.account',
            'fees.account',
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
            'stock_account_id' => null,

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

            'cash_account_id' => null,
            'bank_account_id' => null,
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
}private function resolveManualAccount(
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
    $invoice->loadMissing(['items', 'taxes', 'fees']);

    $itemsTotal = round((float) $invoice->items->sum('amount'), 2);

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

 if ($invoice->invoice_type === 'from_receipt') {
    $debitAccountId = $invoice->stock_account_id;
    $note = 'Inventory / Purchases';
} elseif ($invoice->invoice_type === 'manual_inventory') {
    $debitAccountId = $invoice->purchase_account_id;
    $note = 'Purchase Account';
} elseif ($invoice->invoice_type === 'manual_asset') {
    $debitAccountId = $invoice->purchase_account_id;
    $note = 'Fixed Asset';
} else {
    throw new RuntimeException('Invalid purchase invoice type.');
}
    if (! $debitAccountId) {
        throw new RuntimeException('Debit account is required for purchase invoice.');
    }

    $entry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $debitAccountId,
        'debit' => $itemsTotal,
        'credit' => 0,
        'note' => $note,
    ]);

    $debit += $itemsTotal;

    foreach ($invoice->taxes as $tax) {
        $amount = abs((float) $tax->amount);

        if ($amount <= 0) {
            continue;
        }

        $entry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $tax->account_id,
            'debit' => $amount,
            'credit' => 0,
            'note' => 'Input Tax',
        ]);

        $debit += $amount;
    }

    foreach ($invoice->fees as $fee) {
        $amount = abs((float) $fee->amount);

        if ($amount <= 0) {
            continue;
        }

        $entry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $fee->account_id,
            'debit' => $amount,
            'credit' => 0,
            'note' => 'Purchase Fees',
        ]);

        $debit += $amount;
    }

    $discountAmount = abs((float) $invoice->discount_amount);

    if ($discountAmount > 0) {
        $settings = CompanyAccountSetting::where('company_id', $invoice->company_id)
            ->firstOrFail();

        $discountAccountId = $settings->default_indirect_income_account_id;

        if (! $discountAccountId) {
            throw new RuntimeException('Default Indirect Income Account is not configured.');
        }

        $entry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $discountAccountId,
            'debit' => 0,
            'credit' => $discountAmount,
            'note' => 'Purchase Discount',
        ]);

        $credit += $discountAmount;
    }

    $supplierAmount = round(
        $itemsTotal
        + abs((float) $invoice->tax_total)
        + abs((float) $invoice->fees_total)
        - $discountAmount,
        2
    );

    $entry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $invoice->supplier_payable_account_id,
        'debit' => 0,
        'credit' => $supplierAmount,
        'note' => 'Supplier Payable',
    ]);

    $credit += $supplierAmount;

    if (round($debit, 2) !== round($credit, 2)) {
        throw new RuntimeException('Purchase invoice journal entry is not balanced.');
    }

    $entry->update([
        'total_debit' => round($debit, 2),
        'total_credit' => round($credit, 2),
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
    $year = now()->format('Y');
    $prefix = 'PINV-' . $year . '-';

    $last = PurchaseInvoice::withTrashed()
        ->where('company_id', $companyId)
        ->where('invoice_number', 'like', $prefix . '%')
        ->orderByDesc('id')
        ->lockForUpdate()
        ->first();

    $next = 1;

    if ($last && $last->invoice_number) {
        $lastNumber = (int) substr($last->invoice_number, -5);
        $next = $lastNumber + 1;
    }

    return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
}
public function availablePurchaseInvoices(int $companyId, ?string $search = null)
{
   PurchaseInvoice::query()
    ->where('company_id', $companyId)
    ->where('status', 'submitted')
    ->whereIn('invoice_type', [
        'from_receipt',
        'manual_inventory',
    ])

        ->select([
            'id',
            'invoice_number',
            'supplier_invoice_no',
            'supplier_id',
            'posting_date',
            'grand_total',
        ])
        ->latest()
        ->get();
}
   private function generateJournalEntryNumber(int $companyId): string
{
    $year = now()->format('Y');
    $prefix = 'JV-' . $year . '-';

    $last = JournalEntry::where('company_id', $companyId)
        ->where('entry_number', 'like', $prefix . '%')
        ->orderByDesc('id')
        ->lockForUpdate()
        ->first();

    $next = 1;

    if ($last && $last->entry_number) {
        $lastNumber = (int) substr($last->entry_number, -5);
        $next = $lastNumber + 1;
    }

    return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
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