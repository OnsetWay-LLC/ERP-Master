<?php

namespace App\Services\SalesInvoice;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\FeesTemplate;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\StockEntry;
use App\Models\TaxTemplate;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesInvoiceService
{
    public function create(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();
            $companyAccountSettings = CompanyAccountSetting::where('company_id', $company->id)->firstOrFail();
            // 1. حساب الإجماليات والضرائب والرسوم لحظياً
            $totals = $this->calculateTotals($company->id, $data);
            
            // 2. جلب الحسابات المحاسبية الافتراضية إذا تم اختيار الترحيل الافتراضي
            $accounts = $this->resolvePostingAccounts($company->id, $data);

            $salesInvoice = SalesInvoice::query()->create(array_merge($data, [
                'company_id' => $company->id,
                'invoice_number' => $this->generateInvoiceNumber($company->id),
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ], $accounts));

            // 3. حفظ الحركات الفرعية في جداول الجرد والرسوم
            $this->saveInvoiceItems($salesInvoice, $company->id, $data['items']);
            $this->saveInvoiceTaxes($salesInvoice, $company->id, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveInvoiceFees($salesInvoice, $company->id, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $salesInvoice->load(['customer', 'items', 'taxes', 'fees']);
        });
    }
public function update(SalesInvoice $salesInvoice, array $data): SalesInvoice
    {
        // 1. الحماية: يمنع التعديل إذا لم تكن مسودة
        if ($salesInvoice->status !== 'draft') {
            throw new RuntimeException('Only draft invoices can be updated.');
        }

        return DB::transaction(function () use ($salesInvoice, $data) {
            $companyId = $salesInvoice->company_id;

            // 2. إعادة حساب المجاميع وجلب الحسابات بناءً على التعديل الجديد
            $totals = $this->calculateTotals($companyId, $data);
            $accounts = $this->resolvePostingAccounts($companyId, $data);

            // 3. تحديث الفاتورة الرئيسية
            $salesInvoice->update(array_merge([
                'customer_id' => $data['customer_id'],
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'],
                'payment_due_date' => $data['payment_due_date'] ?? null,
                'posting_method' => $data['posting_method'],
                'payment_mode' => $data['payment_mode'],
                'payment_account_id' => $data['payment_account_id'] ?? null,
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
            ], $accounts));

            // 4. حذف السجلات الفرعية القديمة بالكامل
            $salesInvoice->items()->delete();
            $salesInvoice->taxes()->delete();
            $salesInvoice->fees()->delete();

            // 5. حفظ السجلات الفرعية الجديدة
            $this->saveInvoiceItems($salesInvoice, $companyId, $data['items']);
            $this->saveInvoiceTaxes($salesInvoice, $companyId, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveInvoiceFees($salesInvoice, $companyId, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $salesInvoice->fresh()->load(['customer', 'items', 'taxes', 'fees']);
        });
    }
    public function submit(SalesInvoice $salesInvoice): SalesInvoice
    {
        if ($salesInvoice->status !== 'draft') {
            throw new RuntimeException('Only draft invoices can be submitted.');
        }

        return DB::transaction(function () use ($salesInvoice) {
            $salesInvoice->load(['items.item', 'taxes', 'fees']);

            // أولاً: خصم الكميات من المخازن فورياً وتحديث قيم الجرد
            foreach ($salesInvoice->items as $item) {
                $stock = WarehouseStock::query()
                    ->where('company_id', $salesInvoice->company_id)
                    ->where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->quantity < $item->quantity) {
                    throw new RuntimeException("Insufficient stock for item: {$item->item_name_en} in the selected warehouse.");
                }

                $stock->decrement('quantity', $item->quantity);
                $stock->update([
                    'stock_value' => $stock->fresh()->quantity * $stock->average_rate
                ]);
            }

            // ثانياً: توليد المستند المخزني (Material Issue) تلقائياً
            $this->createStockMovementEntry($salesInvoice);

            // ثالثاً: ترحيل وإصدار قيد اليومية المالي المزدوج (Double-Entry General Ledger)
            $this->postJournalEntry($salesInvoice);

            $salesInvoice->update(['status' => 'submitted']);

            return $salesInvoice;
        });
    }

   private function resolvePostingAccounts(int $companyId, array $data): array
{
    if ($data['posting_method'] === 'manual') {
        return [
            'receivable_account_id' => $data['receivable_account_id'],
            'sales_account_id'      => $data['sales_account_id'],
            'cogs_account_id'       => $data['cogs_account_id'],
            'stock_account_id'      => $data['stock_account_id'],
        ];
    }

    $settings = CompanyAccountSetting::where('company_id', $companyId)
        ->firstOrFail();

    if (
        !$settings->default_receivable_account_id ||
        !$settings->default_income_account_id ||
        !$settings->default_cogs_account_id ||
        !$settings->default_inventory_account_id
    ) {
        throw new RuntimeException(
            'Default posting accounts are not configured in Company Settings.'
        );
    }

    return [
        'receivable_account_id' => $settings->default_receivable_account_id,
        'sales_account_id'      => $settings->default_income_account_id,
        'cogs_account_id'       => $settings->default_cogs_account_id,
        'stock_account_id'      => $settings->default_inventory_account_id,
    ];
}
    private function calculateTotals(int $companyId, array $data): array
    {
        $netTotal = 0;
        foreach ($data['items'] as $item) {
            $netTotal += $item['quantity'] * $item['rate'];
        }

        // احتساب الضرائب المضافة حسب القوالب
        $taxTotal = 0;
        if (!empty($data['tax_template_ids'])) {
            $templates = TaxTemplate::with('lines')->whereIn('id', $data['tax_template_ids'])->get();
            foreach ($templates as $template) {
                foreach ($template->lines as $line) {
                    $taxTotal += $line->type === 'on_net_total' 
                        ? $netTotal * (($line->tax_rate ?? 0) / 100) 
                        : ($line->amount ?? 0);
                }
            }
        }

        // احتساب رسوم الخدمات والمصاريف الإضافية
        $feesTotal = 0;
        if (!empty($data['fees_template_ids'])) {
            $fees = FeesTemplate::whereIn('id', $data['fees_template_ids'])->get();
            foreach ($fees as $fee) {
                $feesTotal += $fee->type === 'percentage' 
                    ? $netTotal * (($fee->fees_rate ?? 0) / 100) 
                    : ($fee->amount ?? 0);
            }
        }

        $discountPercentage = $data['discount_percentage'] ?? 0;
        $discountAmount = $netTotal * ($discountPercentage / 100);
        $grandTotal = ($netTotal + $taxTotal + $feesTotal) - $discountAmount;

        return [
            'net_total' => $netTotal,
            'tax_total' => $taxTotal,
            'fees_total' => $feesTotal,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }
private function generateJournalEntryNumber(int $companyId): string
{
    $lastEntry = JournalEntry::where('company_id', $companyId)
        ->orderByDesc('id')
        ->first();

    if (!$lastEntry) {
        return 'JV-' . now()->year . '-00001';
    }

    $parts = explode('-', $lastEntry->entry_number);

    $nextNumber = ((int) end($parts)) + 1;

    return 'JV-' . now()->year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}
 private function postJournalEntry(SalesInvoice $invoice): void
{
   
   $journalEntry = JournalEntry::create([
    'company_id'    => $invoice->company_id,
    'entry_number'  => $this->generateJournalEntryNumber($invoice->company_id),
    'entry_date'    => $invoice->posting_date,
    'total_debit'   => 0,
    'total_credit'  => 0,
    'description'   => 'فاتورة مبيعات رقم - ' . $invoice->invoice_number,
    'status'        => 'draft',
    'created_by'    => auth('api')->id(),
]);

    $totalDebit = 0;
    $totalCredit = 0;

    $debitAccount = $invoice->payment_mode === 'credit'
        ? $invoice->receivable_account_id
        : $invoice->payment_account_id;

    $journalEntry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $debitAccount,
        'debit'      => $invoice->grand_total,
        'credit'     => 0,
        'note'       => 'حساب العميل / الدفع للفاتورة ' . $invoice->invoice_number,
    ]);

    $totalDebit += $invoice->grand_total;

    $salesAmount = $invoice->net_total - $invoice->discount_amount;

    $journalEntry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $invoice->sales_account_id,
        'debit'      => 0,
        'credit'     => $salesAmount,
        'note'       => 'إيرادات المبيعات',
    ]);

    $totalCredit += $salesAmount;

    foreach ($invoice->taxes as $tax) {

        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $tax->account_id,
            'debit'      => 0,
            'credit'     => $tax->amount,
            'note'       => $tax->title,
        ]);

        $totalCredit += $tax->amount;
    }

    foreach ($invoice->fees as $fee) {

        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $fee->account_id,
            'debit'      => 0,
            'credit'     => $fee->amount,
            'note'       => $fee->title,
        ]);

        $totalCredit += $fee->amount;
    }

    $totalCost = 0;

    foreach ($invoice->items as $item) {

        $stock = WarehouseStock::where('company_id', $invoice->company_id)
            ->where('item_id', $item->item_id)
            ->where('warehouse_id', $item->warehouse_id)
            ->first();

        $avgRate = $stock?->average_rate ?? 0;

        $totalCost += ($item->quantity * $avgRate);
    }

    if (
        $totalCost > 0 &&
        $invoice->cogs_account_id &&
        $invoice->stock_account_id
    ) {

        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $invoice->cogs_account_id,
            'debit'      => $totalCost,
            'credit'     => 0,
            'note'       => 'تكلفة البضاعة المباعة',
        ]);

        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $invoice->stock_account_id,
            'debit'      => 0,
            'credit'     => $totalCost,
            'note'       => 'المخزون',
        ]);

        $totalDebit += $totalCost;
        $totalCredit += $totalCost;
    }

    $journalEntry->update([
        'total_debit'  => $totalDebit,
        'total_credit' => $totalCredit,
    ]);
}

    private function saveInvoiceItems(SalesInvoice $invoice, int $companyId, array $items): void
    {
        foreach ($items as $row) {
            $item = Item::findOrFail($row['item_id']);
            $invoice->items()->create([
                'item_id' => $row['item_id'],
                'warehouse_id' => $row['warehouse_id'],
                'item_code' => $item->item_code,
                'item_name_ar' => $item->name_ar,
                'item_name_en' => $item->name_en,
                'quantity' => $row['quantity'],
                'rate' => $row['rate'],
                'amount' => $row['quantity'] * $row['rate'],
            ]);
        }
    }

    private function saveInvoiceTaxes(SalesInvoice $invoice, int $companyId, array $ids, float $netTotal): void
    {
        if (empty($ids)) return;
        $templates = TaxTemplate::with('lines')->whereIn('id', $ids)->get();
        foreach ($templates as $template) {
            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total' ? $netTotal * (($line->tax_rate ?? 0) / 100) : ($line->amount ?? 0);
                $invoice->taxes()->create([
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

    private function saveInvoiceFees(SalesInvoice $invoice, int $companyId, array $ids, float $netTotal): void
    {
        if (empty($ids)) return;
        $templates = FeesTemplate::whereIn('id', $ids)->get();
        foreach ($templates as $template) {
            $amount = $template->type === 'percentage' ? $netTotal * (($template->fees_rate ?? 0) / 100) : ($template->amount ?? 0);
            $invoice->fees()->create([
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ]);
        }
    }

    private function createStockMovementEntry(SalesInvoice $invoice): void
    {
        // توليد سند مستودعات مخزني رسمي بنجاح لتوثيق خروج البضاعة الصادرة
        StockEntry::query()->create([
            'company_id' => $invoice->company_id,
            'series' => 'SINV-ST-' . now()->format('Y') . '-' . rand(1000, 9999),
            'entry_type' => 'material_issue',
            'posting_date' => $invoice->posting_date,
            'posting_time' => $invoice->posting_time,
            'status' => 'submitted',
            'created_by' => auth('api')->id(),
        ]);
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $count = SalesInvoice::where('company_id', $companyId)->count() + 1;
        return 'INV-' . now()->format('Y') . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
    }
    public function delete(SalesInvoice $salesInvoice): void
    {
        // 1. الحماية: يمنع الحذف إذا لم تكن مسودة
        if ($salesInvoice->status !== 'draft') {
            throw new RuntimeException('Only draft invoices can be deleted.');
        }

        DB::transaction(function () use ($salesInvoice) {
            // 2. حذف السجلات المرتبطة
            $salesInvoice->items()->delete();
            $salesInvoice->taxes()->delete();
            $salesInvoice->fees()->delete();
            
            // 3. حذف الفاتورة نفسها
            $salesInvoice->delete();
        });
    }
}