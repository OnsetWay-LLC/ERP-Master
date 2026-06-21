<?php

namespace App\Services\PurchaseReturns;

use App\Models\CompanyAccountSetting;
use App\Models\FeesTemplate;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\TaxTemplate;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function create(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $invoice = PurchaseInvoice::with('items')
                ->lockForUpdate()
                ->findOrFail($data['purchase_invoice_id']);
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $invoice->company_id,
        $data['posting_date'],
        'create'
    );
            $this->validateInvoice($invoice);

            [$purchaseAccountId, $supplierAccountId] = $this->resolveAccounts($invoice, $data);

            $purchaseReturn = PurchaseReturn::create([
                'series' => $this->generateSeries(),
                'purchase_invoice_id' => $invoice->id,
                'supplier_id' => $invoice->supplier_id,
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'] ?? null,
                'payment_due_date' => $data['payment_due_date'] ?? null,
                'rejected_warehouse_id' => $data['rejected_warehouse_id'] ?? null,
                'target_warehouse_id' => $data['target_warehouse_id'] ?? null,
                'use_default_account' => $data['use_default_account'],
                'purchase_account_id' => $purchaseAccountId,
                'supplier_account_id' => $supplierAccountId,
                'tax_template_id' => $data['tax_template_id'] ?? null,
                'fees_template_id' => $data['fees_template_id'] ?? null,
                'apply_additional_discount_on' => $data['apply_additional_discount_on'] ?? null,
                'additional_discount_percentage' => $data['additional_discount_percentage'] ?? 0,
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            $this->syncItemsAndTotals($purchaseReturn, $data, $invoice);

            return $this->loadFull($purchaseReturn);
        });
    }

    public function update(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn, $data) {
            $purchaseReturn = PurchaseReturn::lockForUpdate()->findOrFail($purchaseReturn->id);
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $purchaseReturn->company_id,
        $data['posting_date'] ?? $purchaseReturn->posting_date,
        'update'
    );
            if ($purchaseReturn->status !== 'draft') {
                abort(422, 'Only draft Purchase Return can be edited.');
            }

            $invoice = PurchaseInvoice::with('items')
                ->lockForUpdate()
                ->findOrFail($purchaseReturn->purchase_invoice_id);

            [$purchaseAccountId, $supplierAccountId] = $this->resolveAccounts($invoice, $data);

            $purchaseReturn->update([
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'] ?? null,
                'payment_due_date' => $data['payment_due_date'] ?? null,
                'rejected_warehouse_id' => $data['rejected_warehouse_id'] ?? null,
                'target_warehouse_id' => $data['target_warehouse_id'] ?? null,
                'use_default_account' => $data['use_default_account'],
                'purchase_account_id' => $purchaseAccountId,
                'supplier_account_id' => $supplierAccountId,
                'tax_template_id' => $data['tax_template_id'] ?? null,
                'fees_template_id' => $data['fees_template_id'] ?? null,
                'apply_additional_discount_on' => $data['apply_additional_discount_on'] ?? null,
                'additional_discount_percentage' => $data['additional_discount_percentage'] ?? 0,
            ]);

            $purchaseReturn->items()->delete();
            $purchaseReturn->taxes()->delete();
            $purchaseReturn->fees()->delete();

            $this->syncItemsAndTotals($purchaseReturn, $data, $invoice);

            return $this->loadFull($purchaseReturn);
        });
    }

    public function submit(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $purchaseReturn->company_id,
        $purchaseReturn->posting_date,
        'create'
    );
        return DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn = PurchaseReturn::lockForUpdate()
                ->with(['items', 'taxes', 'fees', 'purchaseInvoice'])
                ->findOrFail($purchaseReturn->id);

            if ($purchaseReturn->status !== 'draft') {
                abort(422, 'Only draft Purchase Return can be submitted.');
            }

            $journalEntry = $this->createJournalEntry($purchaseReturn);

            $this->applyStockMovement($purchaseReturn);

            $invoice = PurchaseInvoice::lockForUpdate()
                ->findOrFail($purchaseReturn->purchase_invoice_id);

            $returnedAmount = round(
                (float) $invoice->returned_amount + abs((float) $purchaseReturn->grand_total),
                2
            );

            $invoice->update([
                'returned_amount' => $returnedAmount,
                'return_status' => $returnedAmount >= (float) $invoice->grand_total
                    ? 'returned'
                    : 'partially_returned',
                'outstanding_amount' => max(
                    0,
                    round((float) $invoice->outstanding_amount - abs((float) $purchaseReturn->grand_total), 2)
                ),
            ]);

            $purchaseReturn->update([
                'status' => 'submitted',
                'journal_entry_id' => $journalEntry->id,
                'submitted_at' => now(),
            ]);

            return $this->loadFull($purchaseReturn);
        });
    }

    public function cancel(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $purchaseReturn->company_id,
        $purchaseReturn->posting_date,
        'update'
    );
        return DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn = PurchaseReturn::lockForUpdate()
                ->with(['items', 'taxes', 'fees', 'purchaseInvoice'])
                ->findOrFail($purchaseReturn->id);

            if ($purchaseReturn->status !== 'submitted') {
                abort(422, 'Only submitted Purchase Return can be cancelled.');
            }

            $this->createCancelJournalEntry($purchaseReturn);

            $this->reverseStockMovement($purchaseReturn);

            $invoice = PurchaseInvoice::lockForUpdate()
                ->findOrFail($purchaseReturn->purchase_invoice_id);

            $returnedAmount = max(
                0,
                round((float) $invoice->returned_amount - abs((float) $purchaseReturn->grand_total), 2)
            );

            $invoice->update([
                'returned_amount' => $returnedAmount,
                'return_status' => $returnedAmount <= 0
                    ? 'not_returned'
                    : 'partially_returned',
                'outstanding_amount' => round(
                    (float) $invoice->outstanding_amount + abs((float) $purchaseReturn->grand_total),
                    2
                ),
            ]);

            $purchaseReturn->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $this->loadFull($purchaseReturn);
        });
    }

    public function delete(PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn = PurchaseReturn::lockForUpdate()->findOrFail($purchaseReturn->id);

            if ($purchaseReturn->status !== 'draft') {
                abort(422, 'Only draft Purchase Return can be deleted.');
            }

            $purchaseReturn->delete();
        });
    }

    public function getDataFromPurchaseInvoice(PurchaseInvoice $invoice): array
    {
        $invoice->load([
            'supplier',
            'items.item',
            'taxes.accountHead',
            'fees.accountHead',
        ]);

        if ($invoice->status !== 'submitted') {
            abort(422, 'Purchase Return can only be created from submitted Purchase Invoice.');
        }

        $locale = app()->getLocale();

        return [
            'purchase_invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'posting_date' => $invoice->posting_date,
                'payment_due_date' => $invoice->payment_due_date ?? null,
                'grand_total' => (float) $invoice->grand_total,
                'outstanding_amount' => (float) $invoice->outstanding_amount,
                'supplier_payable_account_id' => $invoice->supplier_payable_account_id ?? null,
            ],

            'supplier' => [
                'id' => $invoice->supplier_id,
                'name' => $locale === 'ar'
                    ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
                    : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar),
            ],

            'defaults' => [
                'posting_date' => now()->toDateString(),
                'posting_time' => now()->format('H:i'),
                'payment_due_date' => $invoice->payment_due_date ?? null,
                'return_against' => $invoice->id,
                'use_default_account' => true,
            ],

            'items' => $invoice->items->map(function ($item) {
                $alreadyReturnedQty = $this->getAlreadyReturnedQty($item->id);
                $remainingQty = max(0, (float) $item->quantity - $alreadyReturnedQty);

                return [
                    'purchase_invoice_item_id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item?->name_en ?? $item->item?->name_ar ?? null,
                    'barcode' => $item->barcode ?? null,

                    'original_quantity' => (float) $item->quantity,
                    'already_returned_quantity' => $alreadyReturnedQty,
                    'remaining_quantity' => $remainingQty,

                    'quantity' => -abs($remainingQty),
                    'rate' => (float) $item->rate,
                    'amount' => -abs($remainingQty * (float) $item->rate),
                ];
            })->values(),

            'taxes' => $invoice->taxes->map(function ($tax) {
                return [
                    'type' => $tax->type,
                    'account_head_id' => $tax->account_head_id ?? $tax->account_id ?? null,
                    'account_head_name' => $tax->accountHead?->name_en ?? $tax->accountHead?->name_ar ?? null,
                    'tax_rate' => (float) $tax->tax_rate,
                    'amount' => -abs((float) $tax->amount),
                    'total' => -abs((float) $tax->total),
                ];
            })->values(),

            'fees' => $invoice->fees->map(function ($fee) {
                return [
                    'type' => $fee->type,
                    'account_head_id' => $fee->account_head_id ?? $fee->account_id ?? null,
                    'account_head_name' => $fee->accountHead?->name_en ?? $fee->accountHead?->name_ar ?? null,
                    'fees_rate' => (float) ($fee->fees_rate ?? 0),
                    'amount' => -abs((float) $fee->amount),
                    'total' => -abs((float) $fee->total),
                ];
            })->values(),

            'totals' => [
                'total_quantity' => -abs((float) $invoice->items->sum('quantity')),
                'total_amount' => -abs((float) $invoice->items->sum('amount')),
                'net_total' => -abs((float) $invoice->net_total),
                'tax_total' => -abs((float) $invoice->tax_total),
                'fees_total' => -abs((float) $invoice->fees_total),
                'discount_amount' => -abs((float) $invoice->discount_amount),
                'grand_total' => -abs((float) $invoice->grand_total),
                'outstanding_amount' => (float) $invoice->grand_total,
            ],
        ];
    }

    private function syncItemsAndTotals(PurchaseReturn $purchaseReturn, array $data, PurchaseInvoice $invoice): void
    {
        $totalQty = 0;
        $totalAmount = 0;

        foreach ($data['items'] as $row) {
            $invoiceItem = PurchaseInvoiceItem::where('purchase_invoice_id', $invoice->id)
                ->where('id', $row['purchase_invoice_item_id'])
                ->firstOrFail();

            $returnQty = (float) $row['quantity'];

            $alreadyReturnedQty = $this->getAlreadyReturnedQty(
                purchaseInvoiceItemId: $invoiceItem->id,
                exceptPurchaseReturnId: $purchaseReturn->id
            );

            $remainingQty = (float) $invoiceItem->quantity - $alreadyReturnedQty;

            if ($returnQty > $remainingQty) {
                abort(422, 'Returned quantity cannot be greater than remaining invoice quantity.');
            }

            $negativeQty = -abs($returnQty);
            $rate = (float) $invoiceItem->rate;
            $amount = round($negativeQty * $rate, 2);

            $purchaseReturn->items()->create([
                'purchase_invoice_item_id' => $invoiceItem->id,
                'item_id' => $invoiceItem->item_id,
                'barcode' => $invoiceItem->barcode ?? null,
                'original_quantity' => $invoiceItem->quantity,
                'quantity' => $negativeQty,
                'rate' => $rate,
                'amount' => $amount,
            ]);

            $totalQty += $negativeQty;
            $totalAmount += $amount;
        }

        $discountAmount = $this->calculateDiscount(
            totalAmount: $totalAmount,
            netTotal: $totalAmount,
            data: $data
        );

        $netTotal = round($totalAmount - $discountAmount, 2);

        $taxTotal = $this->syncTaxes($purchaseReturn, $data['tax_template_id'] ?? null, $netTotal);
        $feesTotal = $this->syncFees($purchaseReturn, $data['fees_template_id'] ?? null, $netTotal);

        $grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);

        $purchaseReturn->update([
            'total_quantity' => round($totalQty, 2),
            'total_amount' => round($totalAmount, 2),
            'additional_discount_amount' => round($discountAmount, 2),
            'net_total' => $netTotal,
            'tax_total' => $taxTotal,
            'fees_total' => $feesTotal,
            'grand_total' => $grandTotal,
            'outstanding_amount' => abs($grandTotal),
        ]);
    }

    private function getAlreadyReturnedQty(
        int $purchaseInvoiceItemId,
        ?int $exceptPurchaseReturnId = null
    ): float {
        return (float) PurchaseReturnItem::where('purchase_invoice_item_id', $purchaseInvoiceItemId)
            ->whereHas('purchaseReturn', function ($query) use ($exceptPurchaseReturnId) {
                $query->where('status', 'submitted');

                if ($exceptPurchaseReturnId) {
                    $query->where('id', '!=', $exceptPurchaseReturnId);
                }
            })
            ->sum(DB::raw('ABS(quantity)'));
    }

    private function calculateDiscount(float $totalAmount, float $netTotal, array $data): float
    {
        $percentage = (float) ($data['additional_discount_percentage'] ?? 0);

        if ($percentage <= 0) {
            return 0;
        }

        $base = ($data['apply_additional_discount_on'] ?? null) === 'net_total'
            ? abs($netTotal)
            : abs($totalAmount);

        return -round($base * ($percentage / 100), 2);
    }

    private function syncTaxes(PurchaseReturn $purchaseReturn, ?int $taxTemplateId, float $baseAmount): float
    {
        if (! $taxTemplateId) {
            return 0;
        }

        $template = TaxTemplate::with('lines')->findOrFail($taxTemplateId);

        $totalTax = 0;

        foreach ($template->lines as $line) {
            $amount = -round(abs($baseAmount) * ((float) $line->tax_rate / 100), 2);
            $total = round($baseAmount + $amount, 2);

            $purchaseReturn->taxes()->create([
                'tax_template_line_id' => $line->id,
                'type' => $line->type,
                'account_head_id' => $line->account_id,
                'tax_rate' => $line->tax_rate,
                'amount' => $amount,
                'total' => $total,
            ]);

            $totalTax += $amount;
        }

        return round($totalTax, 2);
    }

    private function syncFees(PurchaseReturn $purchaseReturn, ?int $feesTemplateId, float $baseAmount): float
    {
        if (! $feesTemplateId) {
            return 0;
        }

        $template = FeesTemplate::findOrFail($feesTemplateId);

        $amount = $template->type === 'percentage'
            ? -round(abs($baseAmount) * ((float) $template->fees_rate / 100), 2)
            : -abs((float) $template->amount);

        $purchaseReturn->fees()->create([
            'fees_template_id' => $template->id,
            'type' => $template->type,
            'account_head_id' => $template->account_id,
            'fees_rate' => $template->fees_rate ?? 0,
            'amount' => $amount,
            'total' => round($baseAmount + $amount, 2),
        ]);

        return round($amount, 2);
    }

    private function applyStockMovement(PurchaseReturn $purchaseReturn): void
    {
        if (! $purchaseReturn->target_warehouse_id || ! $purchaseReturn->rejected_warehouse_id) {
            abort(422, 'Target warehouse and rejected warehouse are required.');
        }

        foreach ($purchaseReturn->items as $item) {
            $qty = abs((float) $item->quantity);

            $sourceStock = WarehouseStock::where('warehouse_id', $purchaseReturn->target_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || (float) $sourceStock->quantity < $qty) {
                abort(422, 'Not enough stock in target warehouse for returned item.');
            }

            $rate = (float) $sourceStock->average_rate;
            $sourceNewQty = round((float) $sourceStock->quantity - $qty, 2);

            $sourceStock->update([
                'quantity' => $sourceNewQty,
                'stock_value' => round($sourceNewQty * $rate, 2),
            ]);

            $rejectedStock = WarehouseStock::where('warehouse_id', $purchaseReturn->rejected_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if ($rejectedStock) {
                $newQty = round((float) $rejectedStock->quantity + $qty, 2);

                $rejectedStock->update([
                    'quantity' => $newQty,
                    'average_rate' => $rate,
                    'stock_value' => round($newQty * $rate, 2),
                ]);
            } else {
                $payload = [
                    'warehouse_id' => $purchaseReturn->rejected_warehouse_id,
                    'item_id' => $item->item_id,
                    'quantity' => $qty,
                    'average_rate' => $rate,
                    'stock_value' => round($qty * $rate, 2),
                ];

                if (isset($sourceStock->company_id)) {
                    $payload['company_id'] = $sourceStock->company_id;
                }

                WarehouseStock::create($payload);
            }
        }
    }

    private function reverseStockMovement(PurchaseReturn $purchaseReturn): void
    {
        if (! $purchaseReturn->target_warehouse_id || ! $purchaseReturn->rejected_warehouse_id) {
            abort(422, 'Target warehouse and rejected warehouse are required.');
        }

        foreach ($purchaseReturn->items as $item) {
            $qty = abs((float) $item->quantity);

            $rejectedStock = WarehouseStock::where('warehouse_id', $purchaseReturn->rejected_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if (! $rejectedStock || (float) $rejectedStock->quantity < $qty) {
                abort(422, 'Not enough stock in rejected warehouse to cancel this return.');
            }

            $rate = (float) $rejectedStock->average_rate;
            $rejectedNewQty = round((float) $rejectedStock->quantity - $qty, 2);

            $rejectedStock->update([
                'quantity' => $rejectedNewQty,
                'stock_value' => round($rejectedNewQty * $rate, 2),
            ]);

            $targetStock = WarehouseStock::where('warehouse_id', $purchaseReturn->target_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if ($targetStock) {
                $newQty = round((float) $targetStock->quantity + $qty, 2);

                $targetStock->update([
                    'quantity' => $newQty,
                    'average_rate' => $rate,
                    'stock_value' => round($newQty * $rate, 2),
                ]);
            } else {
                $payload = [
                    'warehouse_id' => $purchaseReturn->target_warehouse_id,
                    'item_id' => $item->item_id,
                    'quantity' => $qty,
                    'average_rate' => $rate,
                    'stock_value' => round($qty * $rate, 2),
                ];

                if (isset($rejectedStock->company_id)) {
                    $payload['company_id'] = $rejectedStock->company_id;
                }

                WarehouseStock::create($payload);
            }
        }
    }

    private function createJournalEntry(PurchaseReturn $purchaseReturn): JournalEntry
    {
        $amount = abs((float) $purchaseReturn->grand_total);

        $journalEntry = JournalEntry::create([
            'entry_number' => $this->generateJournalEntryNumber(),
            'entry_date' => $purchaseReturn->posting_date,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
            'posted_at' => now(),
        ]);

        $lines = [];

        $lines[] = [
            'account_id' => $purchaseReturn->supplier_account_id,
            'debit' => $amount,
            'credit' => 0,
            'note' => 'Purchase Return: ' . $purchaseReturn->series,
        ];

        foreach ($purchaseReturn->taxes as $tax) {
            $lines[] = [
                'account_id' => $tax->account_head_id,
                'debit' => 0,
                'credit' => abs((float) $tax->amount),
                'note' => 'Purchase Return Tax: ' . $purchaseReturn->series,
            ];
        }

        foreach ($purchaseReturn->fees as $fee) {
            $lines[] = [
                'account_id' => $fee->account_head_id,
                'debit' => 0,
                'credit' => abs((float) $fee->amount),
                'note' => 'Purchase Return Fees: ' . $purchaseReturn->series,
            ];
        }

        $lines[] = [
            'account_id' => $purchaseReturn->purchase_account_id,
            'debit' => 0,
            'credit' => abs((float) $purchaseReturn->net_total),
            'note' => 'Purchase Return Purchase Account: ' . $purchaseReturn->series,
        ];

        $journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }

    private function createCancelJournalEntry(PurchaseReturn $purchaseReturn): JournalEntry
    {
        $amount = abs((float) $purchaseReturn->grand_total);

        $journalEntry = JournalEntry::create([
            'entry_number' => $this->generateJournalEntryNumber(),
            'entry_date' => now()->toDateString(),
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
            'posted_at' => now(),
        ]);

        $lines = [];

        foreach ($purchaseReturn->taxes as $tax) {
            $lines[] = [
                'account_id' => $tax->account_head_id,
                'debit' => abs((float) $tax->amount),
                'credit' => 0,
                'note' => 'Cancel Purchase Return Tax: ' . $purchaseReturn->series,
            ];
        }

        foreach ($purchaseReturn->fees as $fee) {
            $lines[] = [
                'account_id' => $fee->account_head_id,
                'debit' => abs((float) $fee->amount),
                'credit' => 0,
                'note' => 'Cancel Purchase Return Fees: ' . $purchaseReturn->series,
            ];
        }

        $lines[] = [
            'account_id' => $purchaseReturn->purchase_account_id,
            'debit' => abs((float) $purchaseReturn->net_total),
            'credit' => 0,
            'note' => 'Cancel Purchase Return Purchase Account: ' . $purchaseReturn->series,
        ];

        $lines[] = [
            'account_id' => $purchaseReturn->supplier_account_id,
            'debit' => 0,
            'credit' => $amount,
            'note' => 'Cancel Purchase Return: ' . $purchaseReturn->series,
        ];

        $journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }

    private function validateInvoice(PurchaseInvoice $invoice): void
    {
        if ($invoice->status !== 'submitted') {
            abort(422, 'Purchase Return can only be created from submitted Purchase Invoice.');
        }
    }

    private function resolveAccounts(PurchaseInvoice $invoice, array $data): array
    {
        if (! $data['use_default_account']) {
            if (empty($data['purchase_account_id']) || empty($data['supplier_account_id'])) {
                abort(422, 'Purchase account and supplier account are required when default account is disabled.');
            }

            return [
                $data['purchase_account_id'],
                $data['supplier_account_id'],
            ];
        }

        $settings = CompanyAccountSetting::first();

        if (! $settings) {
            abort(422, 'Company account settings are not configured.');
        }

        $purchaseAccountId = $settings->default_direct_expense_account_id;
        $supplierAccountId = $invoice->supplier_payable_account_id
            ?? $settings->default_payable_account_id;

        if (! $purchaseAccountId || ! $supplierAccountId) {
            abort(422, 'Default purchase or supplier payable account is not configured.');
        }

        return [$purchaseAccountId, $supplierAccountId];
    }

    private function generateSeries(): string
    {
        $year = now()->format('Y');

        $last = PurchaseReturn::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $next = $last ? ((int) substr($last->series, -5)) + 1 : 1;

        return 'PR-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(): string
    {
        $year = now()->format('Y');

        $last = JournalEntry::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $next = $last ? ((int) substr($last->entry_number, -5)) + 1 : 1;

        return 'JV-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function loadFull(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        return $purchaseReturn->load([
            'purchaseInvoice',
            'supplier',
            'items.item',
            'taxes.accountHead',
            'fees.accountHead',
            'purchaseAccount',
            'supplierAccount',
            'journalEntry.lines.account',
            'creator',
        ]);
    }
}