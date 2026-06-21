<?php

namespace App\Services\SalesReturn;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\FeesTemplate;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesReturn;
use App\Models\TaxTemplate;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesReturnService
{
    public function create(array $data): SalesReturn
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            $invoice = SalesInvoice::with(['items', 'taxes', 'fees'])
                ->where('id', $data['sales_invoice_id'])
                ->firstOrFail();

            if ($invoice->status !== 'submitted') {
                throw new RuntimeException('Sales return can only be created from submitted sales invoice.');
            }

            $accounts = $this->resolveAccounts($company->id, $data, $invoice);
            $totals = $this->calculateTotals($company->id, $data);
$outstanding = (float) $invoice->outstanding_amount;

if ($outstanding <= 0 && (float) $invoice->paid_amount <= 0) {
    $outstanding = (float) $invoice->grand_total;
}
            $salesReturn = SalesReturn::create([
                'company_id' => $company->id,
                'sales_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'return_number' => $this->generateReturnNumber($company->id),
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'],
                'payment_due_date' => $data['payment_due_date'] ?? null,
                'return_reason' => $data['return_reason'] ?? null,
                'posting_method' => $data['posting_method'],
                'sales_account_id' => $accounts['sales_account_id'],
                'customer_account_id' => $accounts['customer_account_id'],
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_apply_on' => $data['discount_apply_on'] ?? 'grand_total',
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
                
                'outstanding_amount' => $outstanding,
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            $this->saveItems($salesReturn, $data['items']);
            $this->saveTaxes($salesReturn, $company->id, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveFees($salesReturn, $company->id, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $salesReturn->fresh()->load([
                'salesInvoice',
                'customer',
                'items.item',
                'items.warehouse',
                'taxes.account',
                'fees.account',
            ]);
        });
    }
public function update(SalesReturn $salesReturn, array $data): SalesReturn
{
    if ($salesReturn->status !== 'draft') {
        throw new RuntimeException('Only draft sales returns can be updated.');
    }

    return DB::transaction(function () use ($salesReturn, $data) {
        $companyId = $salesReturn->company_id;

        $invoice = SalesInvoice::with(['items', 'taxes', 'fees'])
            ->where('id', $data['sales_invoice_id'])
            ->firstOrFail();

        if ($invoice->status !== 'submitted') {
            throw new RuntimeException('Sales return can only be created from submitted sales invoice.');
        }

        $accounts = $this->resolveAccounts($companyId, $data, $invoice);
        $totals = $this->calculateTotals($companyId, $data);

        $outstanding = (float) $invoice->outstanding_amount;

        if ($outstanding <= 0 && (float) $invoice->paid_amount <= 0) {
            $outstanding = (float) $invoice->grand_total;
        }

        $salesReturn->update([
            'sales_invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'posting_date' => $data['posting_date'],
            'posting_time' => $data['posting_time'],
            'payment_due_date' => $data['payment_due_date'] ?? null,
            'return_reason' => $data['return_reason'] ?? null,
            'posting_method' => $data['posting_method'],
            'sales_account_id' => $accounts['sales_account_id'],
            'customer_account_id' => $accounts['customer_account_id'],
            'net_total' => $totals['net_total'],
            'tax_total' => $totals['tax_total'],
            'fees_total' => $totals['fees_total'],
            'discount_apply_on' => $data['discount_apply_on'] ?? 'grand_total',
            'discount_percentage' => $data['discount_percentage'] ?? 0,
            'discount_amount' => $totals['discount_amount'],
            'grand_total' => $totals['grand_total'],
            'outstanding_amount' => $outstanding,
        ]);

        $salesReturn->items()->delete();
        $salesReturn->taxes()->delete();
        $salesReturn->fees()->delete();

        $this->saveItems($salesReturn, $data['items']);
        $this->saveTaxes($salesReturn, $companyId, $data['tax_template_ids'] ?? [], $totals['net_total']);
        $this->saveFees($salesReturn, $companyId, $data['fees_template_ids'] ?? [], $totals['net_total']);

        return $salesReturn->fresh()->load([
            'salesInvoice',
            'customer',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
        ]);
    });
}
public function delete(SalesReturn $salesReturn): void
{
    if ($salesReturn->status !== 'draft') {
        throw new RuntimeException('Only draft sales returns can be deleted.');
    }

    DB::transaction(function () use ($salesReturn) {
        $salesReturn->items()->delete();
        $salesReturn->taxes()->delete();
        $salesReturn->fees()->delete();
        $salesReturn->delete();
    });
}
    public function submit(SalesReturn $salesReturn): SalesReturn
    {
        if ($salesReturn->status !== 'draft') {
            throw new RuntimeException('Only draft sales returns can be submitted.');
        }

        return DB::transaction(function () use ($salesReturn) {
            $salesReturn->load(['items', 'taxes', 'fees', 'salesInvoice']);

            $invoice = SalesInvoice::query()
                ->where('id', $salesReturn->sales_invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->increaseStock($salesReturn);

            $journalEntry = $this->createJournalEntry($salesReturn);

            $newCreditNoteAmount =
                (float) $invoice->credit_note_amount + (float) $salesReturn->grand_total;

            $newOutstanding =
                (float) $invoice->grand_total
                - (float) $invoice->paid_amount
                - $newCreditNoteAmount;

            $invoice->update([
                'credit_note_amount' => $newCreditNoteAmount,
                'outstanding_amount' => max($newOutstanding, 0),
                'payment_status' => $this->paymentStatus($newOutstanding),
            ]);

            $salesReturn->update([
                'status' => 'submitted',
                'journal_entry_id' => $journalEntry->id,
                'outstanding_amount' => max($newOutstanding, 0),
            ]);

            return $salesReturn->fresh()->load([
                'salesInvoice',
                'customer',
                'items.item',
                'items.warehouse',
                'taxes.account',
                'fees.account',
                'journalEntry',
            ]);
        });
    }
    private function decreaseReturnedStock(SalesReturn $salesReturn): void
{
    foreach ($salesReturn->items as $item) {
        $stock = WarehouseStock::query()
            ->where('company_id', $salesReturn->company_id)
            ->where('item_id', $item->item_id)
            ->where('warehouse_id', $item->warehouse_id)
            ->lockForUpdate()
            ->firstOrFail();

        $qty = (float) $item->returned_qty;

        if ((float) $stock->quantity < $qty) {
            throw new RuntimeException('Cannot cancel sales return. Stock quantity is not enough.');
        }

        $newQuantity = (float) $stock->quantity - $qty;
        $averageRate = (float) $stock->average_rate;

        $stock->update([
            'quantity' => $newQuantity,
            'stock_value' => $newQuantity * $averageRate,
        ]);
    }
}
private function createReverseJournalEntry(SalesReturn $salesReturn): JournalEntry
{
    $settings = CompanyAccountSetting::where('company_id', $salesReturn->company_id)->first();

    $journalEntry = JournalEntry::create([
        'company_id' => $salesReturn->company_id,
        'entry_number' => $this->generateJournalEntryNumber($salesReturn->company_id),
        'entry_date' => now()->toDateString(),
        'total_debit' => 0,
        'total_credit' => 0,
        'description' => 'Reverse Sales Return - ' . $salesReturn->return_number,
        'status' => 'posted',
        'created_by' => auth('api')->id(),
    ]);

    $totalDebit = 0;
    $totalCredit = 0;

    $journalEntry->lines()->create([
        'company_id' => $salesReturn->company_id,
        'account_id' => $salesReturn->customer_account_id,
        'debit' => $salesReturn->grand_total,
        'credit' => 0,
        'note' => 'Reverse customer receivable reduction',
    ]);
    $totalDebit += $salesReturn->grand_total;

    $journalEntry->lines()->create([
        'company_id' => $salesReturn->company_id,
        'account_id' => $salesReturn->sales_account_id,
        'debit' => 0,
        'credit' => $salesReturn->net_total,
        'note' => 'Reverse sales return revenue',
    ]);
    $totalCredit += $salesReturn->net_total;

    foreach ($salesReturn->taxes as $tax) {
        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $tax->account_id,
            'debit' => 0,
            'credit' => $tax->amount,
            'note' => 'Reverse sales return tax',
        ]);
        $totalCredit += $tax->amount;
    }

    foreach ($salesReturn->fees as $fee) {
        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $fee->account_id,
            'debit' => 0,
            'credit' => $fee->amount,
            'note' => 'Reverse sales return fee',
        ]);
        $totalCredit += $fee->amount;
    }

    if ((float) $salesReturn->discount_amount > 0) {
        $discountAccountId = $settings?->default_payment_discount_account_id;

        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $discountAccountId,
            'debit' => $salesReturn->discount_amount,
            'credit' => 0,
            'note' => 'Reverse sales return discount',
        ]);

        $totalDebit += $salesReturn->discount_amount;
    }

    $cost = $this->calculateReturnedCost($salesReturn);

    if ($cost > 0) {
        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $settings->default_cogs_account_id,
            'debit' => $cost,
            'credit' => 0,
            'note' => 'Reverse returned COGS',
        ]);

        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $settings->default_inventory_account_id,
            'debit' => 0,
            'credit' => $cost,
            'note' => 'Reverse returned inventory',
        ]);

        $totalDebit += $cost;
        $totalCredit += $cost;
    }

    if (round($totalDebit, 2) !== round($totalCredit, 2)) {
        throw new RuntimeException('Reverse journal entry is not balanced.');
    }

    $journalEntry->update([
        'total_debit' => $totalDebit,
        'total_credit' => $totalCredit,
    ]);

    return $journalEntry;
}
private function paymentStatusAfterCancel(float $outstanding, float $paidAmount): string
{
    if ($outstanding <= 0) {
        return 'paid';
    }

    if ($paidAmount > 0) {
        return 'partially_paid';
    }

    return 'unpaid';
}
public function cancel(SalesReturn $salesReturn): SalesReturn
{
    if ($salesReturn->status !== 'submitted') {
        throw new RuntimeException('Only submitted sales returns can be cancelled.');
    }

    return DB::transaction(function () use ($salesReturn) {
        $salesReturn->load([
            'items',
            'taxes',
            'fees',
            'salesInvoice',
        ]);

        $invoice = SalesInvoice::query()
            ->where('id', $salesReturn->sales_invoice_id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->decreaseReturnedStock($salesReturn);

        $reverseJournalEntry = $this->createReverseJournalEntry($salesReturn);

        $newCreditNoteAmount =
            (float) $invoice->credit_note_amount - (float) $salesReturn->grand_total;

        $newCreditNoteAmount = max($newCreditNoteAmount, 0);

        $newOutstanding =
            (float) $invoice->grand_total
            - (float) $invoice->paid_amount
            - $newCreditNoteAmount;

        $invoice->update([
            'credit_note_amount' => $newCreditNoteAmount,
            'outstanding_amount' => max($newOutstanding, 0),
            'payment_status' => $this->paymentStatusAfterCancel($newOutstanding, (float) $invoice->paid_amount),
        ]);

        $salesReturn->update([
            'status' => 'cancelled',
            'journal_entry_id' => $reverseJournalEntry->id,
            'outstanding_amount' => max($newOutstanding, 0),
        ]);

        return $salesReturn->fresh()->load([
            'salesInvoice',
            'customer',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
            'journalEntry',
        ]);
    });
}
    private function calculateTotals(int $companyId, array $data): array
    {
        $netTotal = 0;

        foreach ($data['items'] as $row) {
            $invoiceItem = SalesInvoiceItem::findOrFail($row['sales_invoice_item_id']);

            if ((float) $row['returned_qty'] > (float) $invoiceItem->quantity) {
                throw new RuntimeException('Returned quantity cannot exceed original invoice quantity.');
            }

            $netTotal += (float) $row['returned_qty'] * (float) $invoiceItem->rate;
        }

        $taxTotal = 0;

        foreach (($data['tax_template_ids'] ?? []) as $taxTemplateId) {
            $template = TaxTemplate::with('lines')->findOrFail($taxTemplateId);

            foreach ($template->lines as $line) {
                $taxTotal += $line->type === 'on_net_total'
                    ? $netTotal * ((float) $line->tax_rate / 100)
                    : (float) ($line->amount ?? 0);
            }
        }

        $feesTotal = 0;

        foreach (($data['fees_template_ids'] ?? []) as $feesTemplateId) {
            $template = FeesTemplate::findOrFail($feesTemplateId);

            $feesTotal += $template->type === 'percentage'
                ? $netTotal * ((float) $template->fees_rate / 100)
                : (float) ($template->amount ?? 0);
        }

        $discountPercentage = (float) ($data['discount_percentage'] ?? 0);
        $discountApplyOn = $data['discount_apply_on'] ?? 'grand_total';

        $base = $discountApplyOn === 'net_total'
            ? $netTotal
            : ($netTotal + $taxTotal + $feesTotal);

        $discountAmount = $base * ($discountPercentage / 100);

        $grandTotal = ($netTotal + $taxTotal + $feesTotal) - $discountAmount;

        return [
            'net_total' => $netTotal,
            'tax_total' => $taxTotal,
            'fees_total' => $feesTotal,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }

    private function saveItems(SalesReturn $salesReturn, array $items): void
    {
        foreach ($items as $row) {
            $invoiceItem = SalesInvoiceItem::findOrFail($row['sales_invoice_item_id']);

            $salesReturn->items()->create([
                'sales_invoice_item_id' => $invoiceItem->id,
                'item_id' => $invoiceItem->item_id,
                'warehouse_id' => $row['warehouse_id'],
                'item_code' => $invoiceItem->item_code,
                'item_name_ar' => $invoiceItem->item_name_ar,
                'item_name_en' => $invoiceItem->item_name_en,
                'original_qty' => $invoiceItem->quantity,
                'returned_qty' => $row['returned_qty'],
                'rate' => $invoiceItem->rate,
                'amount' => (float) $row['returned_qty'] * (float) $invoiceItem->rate,
            ]);
        }
    }

    private function saveTaxes(SalesReturn $salesReturn, int $companyId, array $ids, float $netTotal): void
    {
        foreach ($ids as $id) {
            $template = TaxTemplate::with('lines')
                ->where('company_id', $companyId)
                ->findOrFail($id);

            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? $netTotal * ((float) $line->tax_rate / 100)
                    : (float) ($line->amount ?? 0);

                $salesReturn->taxes()->create([
                    'tax_template_id' => $template->id,
                    'tax_template_line_id' => $line->id,
                    'title' => $template->title,
                    'type' => $line->type,
                    'account_id' => $line->account_id,
                    'tax_rate' => $line->tax_rate,
                    'amount' => $amount,
                ]);
            }
        }
    }

    private function saveFees(SalesReturn $salesReturn, int $companyId, array $ids, float $netTotal): void
    {
        foreach ($ids as $id) {
            $template = FeesTemplate::where('company_id', $companyId)->findOrFail($id);

            $amount = $template->type === 'percentage'
                ? $netTotal * ((float) $template->fees_rate / 100)
                : (float) ($template->amount ?? 0);

            $salesReturn->fees()->create([
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ]);
        }
    }

    private function increaseStock(SalesReturn $salesReturn): void
    {
        foreach ($salesReturn->items as $item) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'company_id' => $salesReturn->company_id,
                    'item_id' => $item->item_id,
                    'warehouse_id' => $item->warehouse_id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'average_rate' => 0,
                    'stock_value' => 0,
                ]
            );

            $qty = (float) $item->returned_qty;
            $rate = (float) $stock->average_rate;

            $stock->update([
                'quantity' => (float) $stock->quantity + $qty,
                'stock_value' => ((float) $stock->quantity + $qty) * $rate,
            ]);
        }
    }

    private function createJournalEntry(SalesReturn $salesReturn): JournalEntry
    {
        $settings = CompanyAccountSetting::where('company_id', $salesReturn->company_id)->first();

        $journalEntry = JournalEntry::create([
            'company_id' => $salesReturn->company_id,
            'entry_number' => $this->generateJournalEntryNumber($salesReturn->company_id),
            'entry_date' => $salesReturn->posting_date,
            'total_debit' => 0,
            'total_credit' => 0,
            'description' => 'Sales Return - ' . $salesReturn->return_number,
            'status' => 'posted',
            'created_by' => auth('api')->id(),
        ]);

        $totalDebit = 0;
        $totalCredit = 0;

        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $salesReturn->sales_account_id,
            'debit' => $salesReturn->net_total,
            'credit' => 0,
            'note' => 'Sales return revenue reversal',
        ]);
        $totalDebit += $salesReturn->net_total;

        foreach ($salesReturn->taxes as $tax) {
            $journalEntry->lines()->create([
                'company_id' => $salesReturn->company_id,
                'account_id' => $tax->account_id,
                'debit' => $tax->amount,
                'credit' => 0,
                'note' => 'Sales return tax reversal',
            ]);
            $totalDebit += $tax->amount;
        }

        foreach ($salesReturn->fees as $fee) {
            $journalEntry->lines()->create([
                'company_id' => $salesReturn->company_id,
                'account_id' => $fee->account_id,
                'debit' => $fee->amount,
                'credit' => 0,
                'note' => 'Sales return fee reversal',
            ]);
            $totalDebit += $fee->amount;
        }

        if ((float) $salesReturn->discount_amount > 0) {
            $discountAccountId = $settings?->default_payment_discount_account_id;

            $journalEntry->lines()->create([
                'company_id' => $salesReturn->company_id,
                'account_id' => $discountAccountId,
                'debit' => 0,
                'credit' => $salesReturn->discount_amount,
                'note' => 'Reverse sales discount',
            ]);

            $totalCredit += $salesReturn->discount_amount;
        }

        $journalEntry->lines()->create([
            'company_id' => $salesReturn->company_id,
            'account_id' => $salesReturn->customer_account_id,
            'debit' => 0,
            'credit' => $salesReturn->grand_total,
            'note' => 'Reduce customer receivable',
        ]);
        $totalCredit += $salesReturn->grand_total;

        $cost = $this->calculateReturnedCost($salesReturn);

        if ($cost > 0) {
            $journalEntry->lines()->create([
                'company_id' => $salesReturn->company_id,
                'account_id' => $settings->default_inventory_account_id,
                'debit' => $cost,
                'credit' => 0,
                'note' => 'Returned stock',
            ]);

            $journalEntry->lines()->create([
                'company_id' => $salesReturn->company_id,
                'account_id' => $settings->default_cogs_account_id,
                'debit' => 0,
                'credit' => $cost,
                'note' => 'Reverse COGS',
            ]);

            $totalDebit += $cost;
            $totalCredit += $cost;
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new RuntimeException('Journal entry is not balanced.');
        }

        $journalEntry->update([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);

        return $journalEntry;
    }

    private function calculateReturnedCost(SalesReturn $salesReturn): float
    {
        $cost = 0;

        foreach ($salesReturn->items as $item) {
            $stock = WarehouseStock::where('company_id', $salesReturn->company_id)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->warehouse_id)
                ->first();

            $cost += (float) $item->returned_qty * (float) ($stock?->average_rate ?? 0);
        }

        return $cost;
    }

    private function resolveAccounts(int $companyId, array $data, SalesInvoice $invoice): array
    {
        if ($data['posting_method'] === 'manual') {
            return [
                'sales_account_id' => $data['sales_account_id'],
                'customer_account_id' => $data['customer_account_id'],
            ];
        }

        return [
            'sales_account_id' => $invoice->sales_account_id,
            'customer_account_id' => $invoice->receivable_account_id,
        ];
    }

    private function paymentStatus(float $outstanding): string
    {
        if ($outstanding <= 0) {
            return 'paid';
        }

        return 'partially_paid';
    }

    private function generateReturnNumber(int $companyId): string
    {
        $count = SalesReturn::where('company_id', $companyId)->count() + 1;

        return 'SR-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $count = JournalEntry::where('company_id', $companyId)->count() + 1;

        return 'JV-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}