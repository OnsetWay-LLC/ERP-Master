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
        $postingDate = !empty($data['posting_date'])
            ? $data['posting_date']
            : now()->toDateString();

        return DB::transaction(function () use ($data, $postingDate) {
            $invoice = PurchaseInvoice::with('items')
                ->lockForUpdate()
                ->findOrFail($data['purchase_invoice_id']);

            app(\App\Services\FinancialYear\FinancialYearService::class)
                ->validateTransactionDate(
                    $invoice->company_id,
                    $postingDate,
                    'create'
                );

            $this->validateInvoice($invoice);

            $purchaseAccountId = $invoice->purchase_account_id;
            $supplierAccountId = $invoice->supplier_payable_account_id;

            if (! $purchaseAccountId || ! $supplierAccountId) {
                abort(422, 'Purchase invoice accounts are not configured.');
            }

            $purchaseReturn = PurchaseReturn::create([
                'series'                         => $this->generateSeries(),
                'purchase_invoice_id'            => $invoice->id,
                'supplier_id'                    => $invoice->supplier_id,
                'posting_date'                   => $postingDate,
                'posting_time'                   => !empty($data['posting_time'])
                    ? $data['posting_time']
                    : now()->format('H:i:s'),
                'payment_due_date'               => $data['payment_due_date'] ?? null,
                'rejected_warehouse_id'          => $data['rejected_warehouse_id'] ?? null,
                'target_warehouse_id'            => $data['target_warehouse_id'] ?? null,
                'purchase_account_id'            => $purchaseAccountId,
                'supplier_account_id'            => $supplierAccountId,
                'tax_template_id'                => $data['tax_template_id'] ?? null,
                'fees_template_id'               => $data['fees_template_id'] ?? null,
                'apply_additional_discount_on'   => $data['apply_additional_discount_on'] ?? null,
                'additional_discount_percentage' => $data['additional_discount_percentage'] ?? 0,
                'status'                         => 'draft',
                'created_by'                     => auth('api')->id(),
            ]);

            $this->syncItemsAndTotals($purchaseReturn, $data, $invoice);

            return $this->loadFull($purchaseReturn);
        });
    }

   public function update(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
{
    return DB::transaction(function () use ($purchaseReturn, $data) {
        $purchaseReturn = PurchaseReturn::lockForUpdate()
            ->with('purchaseInvoice')
            ->findOrFail($purchaseReturn->id);

        if ($purchaseReturn->status !== 'draft') {
            abort(422, 'Only draft Purchase Return can be edited.');
        }

        $invoice = PurchaseInvoice::with('items')
            ->lockForUpdate()
            ->findOrFail($purchaseReturn->purchase_invoice_id);

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                (int) $invoice->company_id,
                $data['posting_date'] ?? $purchaseReturn->posting_date,
                'update'
            );

        $purchaseAccountId = $invoice->purchase_account_id;
        $supplierAccountId = $invoice->supplier_payable_account_id;

        $purchaseReturn->update([
            'posting_date'                   => $data['posting_date'] ?? $purchaseReturn->posting_date,
            'posting_time'                   => $data['posting_time'] ?? $purchaseReturn->posting_time,
            'payment_due_date'               => $data['payment_due_date'] ?? $purchaseReturn->payment_due_date,
            'rejected_warehouse_id'          => $data['rejected_warehouse_id'] ?? $purchaseReturn->rejected_warehouse_id,
            'target_warehouse_id'            => $data['target_warehouse_id'] ?? $purchaseReturn->target_warehouse_id,
            'purchase_account_id'            => $purchaseAccountId,
            'supplier_account_id'            => $supplierAccountId,
            'tax_template_id'                => $data['tax_template_id'] ?? $purchaseReturn->tax_template_id,
            'fees_template_id'               => $data['fees_template_id'] ?? $purchaseReturn->fees_template_id,
            'apply_additional_discount_on'   => $data['apply_additional_discount_on'] ?? $purchaseReturn->apply_additional_discount_on,
            'additional_discount_percentage' => $data['additional_discount_percentage'] ?? $purchaseReturn->additional_discount_percentage,
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
    return DB::transaction(function () use ($purchaseReturn) {
        $purchaseReturn = PurchaseReturn::lockForUpdate()
            ->with(['items', 'taxes', 'fees', 'purchaseInvoice'])
            ->findOrFail($purchaseReturn->id);

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                (int) $purchaseReturn->purchaseInvoice->company_id,
                $purchaseReturn->posting_date,
                'create'
            );

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
            'returned_amount'    => $returnedAmount,
            'return_status'      => $returnedAmount >= (float) $invoice->grand_total
                ? 'returned'
                : 'partially_returned',
            'outstanding_amount' => max(
                0,
                round((float) $invoice->outstanding_amount - abs((float) $purchaseReturn->grand_total), 2)
            ),
        ]);

        $purchaseReturn->update([
            'status'           => 'submitted',
            'journal_entry_id' => $journalEntry->id,
            'submitted_at'     => now(),
        ]);

        return $this->loadFull($purchaseReturn);
    });
}

    public function cancel(PurchaseReturn $purchaseReturn): PurchaseReturn
{
    return DB::transaction(function () use ($purchaseReturn) {
        $purchaseReturn = PurchaseReturn::lockForUpdate()
            ->with(['items', 'taxes', 'fees', 'purchaseInvoice'])
            ->findOrFail($purchaseReturn->id);

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                (int) $purchaseReturn->purchaseInvoice->company_id,
                $purchaseReturn->posting_date,
                'update'
            );

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
            'returned_amount'    => $returnedAmount,
            'return_status'      => $returnedAmount <= 0
                ? 'not_returned'
                : 'partially_returned',
            'outstanding_amount' => round(
                (float) $invoice->outstanding_amount + abs((float) $purchaseReturn->grand_total),
                2
            ),
        ]);

        $purchaseReturn->update([
            'status'       => 'cancelled',
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
                'id'                          => $invoice->id,
                'invoice_number'              => $invoice->invoice_number,
                'posting_date'                => $invoice->posting_date,
                'payment_due_date'            => $invoice->payment_due_date ?? null,
                'grand_total'                 => (float) $invoice->grand_total,
                'outstanding_amount'          => (float) $invoice->outstanding_amount,
                'supplier_payable_account_id' => $invoice->supplier_payable_account_id ?? null,
            ],

            'supplier' => [
                'id'   => $invoice->supplier_id,
                'name' => $locale === 'ar'
                    ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
                    : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar),
            ],

            'defaults' => [
                'posting_date'     => now()->toDateString(),
                'posting_time'     => now()->format('H:i'),
                'payment_due_date' => $invoice->payment_due_date ?? null,
                'return_against'   => $invoice->id,
            ],

            'items' => $invoice->items->map(function ($item) {
                $alreadyReturnedQty = $this->getAlreadyReturnedQty($item->id);
                $remainingQty       = max(0, (float) $item->quantity - $alreadyReturnedQty);

                return [
                    'purchase_invoice_item_id'  => $item->id,
                    'item_id'                   => $item->item_id,
                    'item_name'                 => $item->item?->name_en ?? $item->item?->name_ar ?? null,
                    'barcode'                   => $item->barcode ?? null,
                    'original_quantity'         => (float) $item->quantity,
                    'already_returned_quantity' => $alreadyReturnedQty,
                    'remaining_quantity'        => $remainingQty,
                    'quantity'                  => -abs($remainingQty),
                    'rate'                      => (float) $item->rate,
                    'amount'                    => -abs($remainingQty * (float) $item->rate),
                ];
            })->values(),

            'taxes' => $invoice->taxes->map(function ($tax) {
                return [
                    'type'              => $tax->type,
                    'account_head_id'   => $tax->account_head_id ?? $tax->account_id ?? null,
                    'account_head_name' => $tax->accountHead?->name_en ?? $tax->accountHead?->name_ar ?? null,
                    'tax_rate'          => (float) $tax->tax_rate,
                    'amount'            => -abs((float) $tax->amount),
                    'total'             => -abs((float) $tax->total),
                ];
            })->values(),

            'fees' => $invoice->fees->map(function ($fee) {
                return [
                    'type'              => $fee->type,
                    'account_head_id'   => $fee->account_head_id ?? $fee->account_id ?? null,
                    'account_head_name' => $fee->accountHead?->name_en ?? $fee->accountHead?->name_ar ?? null,
                    'fees_rate'         => (float) ($fee->fees_rate ?? 0),
                    'amount'            => -abs((float) $fee->amount),
                    'total'             => -abs((float) $fee->total),
                ];
            })->values(),

            'totals' => [
                'total_quantity'     => -abs((float) $invoice->items->sum('quantity')),
                'total_amount'       => -abs((float) $invoice->items->sum('amount')),
                'net_total'          => -abs((float) $invoice->net_total),
                'tax_total'          => -abs((float) $invoice->tax_total),
                'fees_total'         => -abs((float) $invoice->fees_total),
                'discount_amount'    => -abs((float) $invoice->discount_amount),
                'grand_total'        => -abs((float) $invoice->grand_total),
                'outstanding_amount' => (float) $invoice->grand_total,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Core calculation
    // -------------------------------------------------------------------------

    private function syncItemsAndTotals(PurchaseReturn $purchaseReturn, array $data, PurchaseInvoice $invoice): void
    {
        $totalQty    = 0;
        $totalAmount = 0;

        foreach ($data['items'] as $row) {
            $invoiceItem = PurchaseInvoiceItem::where('purchase_invoice_id', $invoice->id)
                ->where('id', $row['purchase_invoice_item_id'])
                ->firstOrFail();

            $returnQty = abs((float) $row['quantity']);

            $alreadyReturnedQty = $this->getAlreadyReturnedQty(
                purchaseInvoiceItemId: $invoiceItem->id,
                exceptPurchaseReturnId: $purchaseReturn->id
            );

            $remainingQty = (float) $invoiceItem->quantity - $alreadyReturnedQty;

            if ($returnQty > $remainingQty) {
                abort(422, 'Returned quantity cannot be greater than remaining invoice quantity.');
            }

            $negativeQty = -abs($returnQty);
            $rate        = (float) $invoiceItem->rate;
            $amount      = round($negativeQty * $rate, 2);

            $purchaseReturn->items()->create([
                'purchase_invoice_item_id' => $invoiceItem->id,
                'item_id'                  => $invoiceItem->item_id,
                'barcode'                  => $invoiceItem->barcode ?? null,
                'original_quantity'        => $invoiceItem->quantity,
                'quantity'                 => $negativeQty,
                'rate'                     => $rate,
                'amount'                   => $amount,
            ]);

            $totalQty    += $negativeQty;
            $totalAmount += $amount;
        }

        // Tax:  actual = fixed amount | percentage = rate × item_total
        $taxTotal = $this->syncTaxes(
            $purchaseReturn,
            $data['tax_template_id'] ?? null,
            $totalAmount
        );

        // Fees: actual = fixed amount | percentage = rate × item_total
        $feesTotal = $this->syncFees(
            $purchaseReturn,
            $data['fees_template_id'] ?? null,
            $totalAmount
        );

        // net_total = item_total − fees
        $netTotal = round($totalAmount - $feesTotal, 2);

        // Discount base:
        //   Grand Total → item_total
        //   Net Total   → item_total − fees  (= net_total)
        $applyOn    = $data['apply_additional_discount_on'] ?? 'grand_total';
        $percentage = (float) ($data['additional_discount_percentage'] ?? 0);

        $discountBase = $applyOn === 'net_total'
            ? abs($netTotal)      // item_total − fees
            : abs($totalAmount);  // item_total only

        $discountAmount = $percentage > 0
            ? -round($discountBase * ($percentage / 100), 2)
            : 0.0;

        // grand_total = item_total + tax + fees − discount
        $grandTotal = round($totalAmount + $taxTotal + $feesTotal - $discountAmount, 2);

        $purchaseReturn->update([
            'total_quantity'             => round($totalQty, 2),
            'total_amount'               => round($totalAmount, 2),
            'tax_total'                  => $taxTotal,
            'fees_total'                 => $feesTotal,
            'net_total'                  => $netTotal,
            'additional_discount_amount' => round($discountAmount, 2),
            'grand_total'                => $grandTotal,
            'outstanding_amount'         => abs((float) $invoice->grand_total),
        ]);
    }

    // -------------------------------------------------------------------------
    // Taxes
    // -------------------------------------------------------------------------

    private function syncTaxes(PurchaseReturn $purchaseReturn, ?int $taxTemplateId, float $baseAmount): float
    {
        if (! $taxTemplateId) {
            return 0;
        }

        $template = TaxTemplate::with('lines')->findOrFail($taxTemplateId);
        $totalTax = 0;

        foreach ($template->lines as $line) {
            $taxRate = (float) ($line->tax_rate ?? $line->rate ?? 0);

            if ($line->type === 'actual') {
                // Fixed amount — added directly
                $amount = -abs((float) ($line->amount ?? 0));
            } else {
                // Percentage — always calculated from item_total
                $amount = -round(abs($baseAmount) * ($taxRate / 100), 2);
            }

            $purchaseReturn->taxes()->create([
                'tax_template_line_id' => $line->id,
                'type'                 => $line->type,
                'account_head_id'      => $line->account_id,
                'tax_rate'             => $taxRate,
                'amount'               => $amount,
                'total'                => round($baseAmount + $amount, 2),
            ]);

            $totalTax += $amount;
        }

        return round($totalTax, 2);
    }

    // -------------------------------------------------------------------------
    // Fees
    // -------------------------------------------------------------------------

    private function syncFees(PurchaseReturn $purchaseReturn, ?int $feesTemplateId, float $baseAmount): float
    {
        if (! $feesTemplateId) {
            return 0;
        }

        $template = FeesTemplate::findOrFail($feesTemplateId);

        if ($template->type === 'actual') {
            // Fixed amount — added directly
            $amount = -abs((float) $template->amount);
        } else {
            // Percentage — always calculated from item_total
            $amount = -round(abs($baseAmount) * ((float) $template->fees_rate / 100), 2);
        }

        $purchaseReturn->fees()->create([
            'fees_template_id' => $template->id,
            'type'             => $template->type,
            'account_head_id'  => $template->account_id,
            'fees_rate'        => $template->fees_rate ?? 0,
            'amount'           => $amount,
            'total'            => round($baseAmount + $amount, 2),
        ]);

        return round($amount, 2);
    }

    // -------------------------------------------------------------------------
    // Already returned qty
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Stock movement
    // -------------------------------------------------------------------------

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

            $rate         = (float) $sourceStock->average_rate;
            $sourceNewQty = round((float) $sourceStock->quantity - $qty, 2);

            $sourceStock->update([
                'quantity'    => $sourceNewQty,
                'stock_value' => round($sourceNewQty * $rate, 2),
            ]);

            $rejectedStock = WarehouseStock::where('warehouse_id', $purchaseReturn->rejected_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if ($rejectedStock) {
                $newQty = round((float) $rejectedStock->quantity + $qty, 2);

                $rejectedStock->update([
                    'quantity'     => $newQty,
                    'average_rate' => $rate,
                    'stock_value'  => round($newQty * $rate, 2),
                ]);
            } else {
                $payload = [
                    'warehouse_id' => $purchaseReturn->rejected_warehouse_id,
                    'item_id'      => $item->item_id,
                    'quantity'     => $qty,
                    'average_rate' => $rate,
                    'stock_value'  => round($qty * $rate, 2),
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

            $rate           = (float) $rejectedStock->average_rate;
            $rejectedNewQty = round((float) $rejectedStock->quantity - $qty, 2);

            $rejectedStock->update([
                'quantity'    => $rejectedNewQty,
                'stock_value' => round($rejectedNewQty * $rate, 2),
            ]);

            $targetStock = WarehouseStock::where('warehouse_id', $purchaseReturn->target_warehouse_id)
                ->where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if ($targetStock) {
                $newQty = round((float) $targetStock->quantity + $qty, 2);

                $targetStock->update([
                    'quantity'     => $newQty,
                    'average_rate' => $rate,
                    'stock_value'  => round($newQty * $rate, 2),
                ]);
            } else {
                $payload = [
                    'warehouse_id' => $purchaseReturn->target_warehouse_id,
                    'item_id'      => $item->item_id,
                    'quantity'     => $qty,
                    'average_rate' => $rate,
                    'stock_value'  => round($qty * $rate, 2),
                ];

                if (isset($rejectedStock->company_id)) {
                    $payload['company_id'] = $rejectedStock->company_id;
                }

                WarehouseStock::create($payload);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Journal entries
    // -------------------------------------------------------------------------

    private function createJournalEntry(PurchaseReturn $purchaseReturn): JournalEntry
    {
        $purchaseReturn->loadMissing(['taxes', 'fees', 'purchaseInvoice']);

        $supplierAmount = abs((float) $purchaseReturn->grand_total);
        $itemsAmount    = abs((float) $purchaseReturn->total_amount);
        $taxAmount      = abs((float) $purchaseReturn->tax_total);
        $feesAmount     = abs((float) $purchaseReturn->fees_total);
        $discountAmount = abs((float) $purchaseReturn->additional_discount_amount);

        // Debit:  supplier (grand_total) + discount  =  items + tax + fees
        // Credit: items + tax + fees
        $totalDebit  = round($supplierAmount + $discountAmount, 2);
        $totalCredit = round($itemsAmount + $taxAmount + $feesAmount, 2);

       $journalEntry = JournalEntry::create([
    'company_id'    => (int) $purchaseReturn->purchaseInvoice->company_id,
    'entry_number' => $this->generateJournalEntryNumber(
    (int) $purchaseReturn->purchaseInvoice->company_id
),
    'entry_date'    => $purchaseReturn->posting_date,
    'total_debit'   => $totalDebit,
    'total_credit'  => $totalCredit,
    'status'        => 'posted',
    'created_by'    => auth('api')->id(),
    'posted_at'     => now(),
]);

        $lines = [];

        $lines[] = [
            'account_id' => $purchaseReturn->supplier_account_id,
            'debit'      => $supplierAmount,
            'credit'     => 0,
            'note'       => 'Purchase Return Supplier Payable: ' . $purchaseReturn->series,
        ];

        if ($discountAmount > 0) {
            $discountAccountId = CompanyAccountSetting::where('company_id', $purchaseReturn->purchaseInvoice->company_id)
                ->value('default_payment_discount_account_id');

            if (! $discountAccountId) {
                abort(422, 'Default payment discount account is not configured.');
            }

            $lines[] = [
                'account_id' => $discountAccountId,
                'debit'      => $discountAmount,
                'credit'     => 0,
                'note'       => 'Purchase Return Discount: ' . $purchaseReturn->series,
            ];
        }

        $lines[] = [
            'account_id' => $purchaseReturn->purchase_account_id,
            'debit'      => 0,
            'credit'     => $itemsAmount,
            'note'       => 'Purchase Return Inventory/Purchase Account: ' . $purchaseReturn->series,
        ];

        foreach ($purchaseReturn->taxes as $tax) {
            $lines[] = [
                'account_id' => $tax->account_head_id,
                'debit'      => 0,
                'credit'     => abs((float) $tax->amount),
                'note'       => 'Purchase Return Tax: ' . $purchaseReturn->series,
            ];
        }

        foreach ($purchaseReturn->fees as $fee) {
            $lines[] = [
                'account_id' => $fee->account_head_id,
                'debit'      => 0,
                'credit'     => abs((float) $fee->amount),
                'note'       => 'Purchase Return Fees: ' . $purchaseReturn->series,
            ];
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            abort(422, 'Purchase Return journal entry is not balanced.');
        }

       $companyId = (int) $purchaseReturn->purchaseInvoice->company_id;

foreach ($lines as &$line) {
    $line['company_id'] = $companyId;
}
unset($line);

$journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }

    private function createCancelJournalEntry(PurchaseReturn $purchaseReturn): JournalEntry
    {
        $purchaseReturn->loadMissing(['taxes', 'fees', 'purchaseInvoice']);

        $supplierAmount = abs((float) $purchaseReturn->grand_total);
        $itemsAmount    = abs((float) $purchaseReturn->total_amount);
        $taxAmount      = abs((float) $purchaseReturn->tax_total);
        $feesAmount     = abs((float) $purchaseReturn->fees_total);
        $discountAmount = abs((float) $purchaseReturn->additional_discount_amount);

        $totalDebit  = round($itemsAmount + $taxAmount + $feesAmount, 2);
        $totalCredit = round($supplierAmount + $discountAmount, 2);

       $journalEntry = JournalEntry::create([
    'company_id'    => (int) $purchaseReturn->purchaseInvoice->company_id,
    'entry_number' => $this->generateJournalEntryNumber(
    (int) $purchaseReturn->purchaseInvoice->company_id
),
    'entry_date'    => now()->toDateString(),
    'total_debit'   => $totalDebit,
    'total_credit'  => $totalCredit,
    'status'        => 'posted',
    'created_by'    => auth('api')->id(),
    'posted_at'     => now(),
]);

        $lines = [];

        $lines[] = [
            'account_id' => $purchaseReturn->purchase_account_id,
            'debit'      => $itemsAmount,
            'credit'     => 0,
            'note'       => 'Cancel Purchase Return Inventory/Purchase Account: ' . $purchaseReturn->series,
        ];

        foreach ($purchaseReturn->taxes as $tax) {
            $lines[] = [
                'account_id' => $tax->account_head_id,
                'debit'      => abs((float) $tax->amount),
                'credit'     => 0,
                'note'       => 'Cancel Purchase Return Tax: ' . $purchaseReturn->series,
            ];
        }

        foreach ($purchaseReturn->fees as $fee) {
            $lines[] = [
                'account_id' => $fee->account_head_id,
                'debit'      => abs((float) $fee->amount),
                'credit'     => 0,
                'note'       => 'Cancel Purchase Return Fees: ' . $purchaseReturn->series,
            ];
        }

        $lines[] = [
            'account_id' => $purchaseReturn->supplier_account_id,
            'debit'      => 0,
            'credit'     => $supplierAmount,
            'note'       => 'Cancel Purchase Return Supplier Payable: ' . $purchaseReturn->series,
        ];

        if ($discountAmount > 0) {
            $discountAccountId = CompanyAccountSetting::where('company_id', $purchaseReturn->purchaseInvoice->company_id)
                ->value('default_payment_discount_account_id');

            if (! $discountAccountId) {
                abort(422, 'Default payment discount account is not configured.');
            }

            $lines[] = [
                'account_id' => $discountAccountId,
                'debit'      => 0,
                'credit'     => $discountAmount,
                'note'       => 'Cancel Purchase Return Discount: ' . $purchaseReturn->series,
            ];
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            abort(422, 'Cancel Purchase Return journal entry is not balanced.');
        }

        $companyId = (int) $purchaseReturn->purchaseInvoice->company_id;

foreach ($lines as &$line) {
    $line['company_id'] = $companyId;
}
unset($line);

$journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validateInvoice(PurchaseInvoice $invoice): void
    {
        if ($invoice->status !== 'submitted') {
            abort(422, 'Purchase Return can only be created from submitted Purchase Invoice.');
        }
    }

    private function generateSeries(): string
    {
        $year = now()->format('Y');
        $last = PurchaseReturn::whereYear('created_at', $year)->latest('id')->first();
        $next = $last ? ((int) substr($last->series, -5)) + 1 : 1;

        return 'PR-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(?int $companyId = null): string
{
    $year = now()->format('Y');

    $query = JournalEntry::whereYear('created_at', $year);

    if ($companyId) {
        $query->where('company_id', $companyId);
    }

    $last = $query
        ->where('entry_number', 'like', 'JV-' . $year . '-%')
        ->orderByRaw("CAST(RIGHT(entry_number, 5) AS INT) DESC")
        ->lockForUpdate()
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