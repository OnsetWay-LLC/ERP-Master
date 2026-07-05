<?php

namespace App\Services\SalesInvoice;

use App\Models\Company;
use App\Models\CompanyAccountSetting;
use App\Models\DiscountApprovalRequest;
use App\Models\DiscountSetting;
use App\Models\FeesTemplate;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\StockEntry;
use App\Models\TaxTemplate;
use App\Models\DeliveryNote;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Notifications\DiscountApprovalRequestedNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesInvoiceService
{
   public function create(array $data): SalesInvoice
{
    $company = Company::query()->firstOrFail();

    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $company->id,
            $data['posting_date'],
            'create'
        );

    return DB::transaction(function () use ($data) {
        $company = Company::query()->firstOrFail();

        $discountComesFromPreviousDocument =
            ! empty($data['sales_order_id']) ||
            ! empty($data['delivery_note_id']);

        if ($discountComesFromPreviousDocument) {
            $discountDecision = [
                'requires_approval' => false,
                'applied_discount_percentage' => (float) ($data['discount_percentage'] ?? 0),
                'requested_discount_percentage' => (float) ($data['discount_percentage'] ?? 0),
                'allowed_discount_percentage' => 0,
            ];
        } else {
            $discountDecision = $this->handleDiscountDecision($company->id, $data);
        }

        $data['discount_percentage'] = $discountDecision['applied_discount_percentage'];

        $totals = $this->calculateTotals($company->id, $data);
        $accounts = $this->resolvePostingAccounts($company->id, $data);

        $salesInvoice = SalesInvoice::query()->create(array_merge([
            'company_id' => $company->id,
            'customer_id' => $data['customer_id'],
            'sales_order_id' => $data['sales_order_id'] ?? null,
            'delivery_note_id' => $data['delivery_note_id'] ?? null,
            'sales_person_id' => $data['sales_person_id'],
            'invoice_number' => $this->generateInvoiceNumber($company->id),

            'posting_date' => $data['posting_date'],
            'posting_time' => $data['posting_time'],
            'payment_due_date' => $data['payment_due_date'] ?? null,

            'posting_method' => $data['posting_method'],

            'payment_mode' => 'credit',
            'payment_account_id' => null,
            'paid_amount' => 0,
            'outstanding_amount' => $totals['grand_total'],
            'payment_status' => 'unpaid',

            'discount_percentage' => $data['discount_percentage'],
            'net_total' => $totals['net_total'],
            'tax_total' => $totals['tax_total'],
            'fees_total' => $totals['fees_total'],
            'discount_amount' => $totals['discount_amount'],
            'grand_total' => $totals['grand_total'],
            'is_asset_sale' => $data['is_asset_sale'] ?? false,

            'status' => 'draft',
            'created_by' => auth('api')->id(),
        ], $accounts));

        $this->saveInvoiceItems($salesInvoice, $company->id, $data['items']);
        $this->saveInvoiceTaxes($salesInvoice, $company->id, $data['tax_template_ids'] ?? [], $totals['net_total']);
        $this->saveInvoiceFees($salesInvoice, $company->id, $data['fees_template_ids'] ?? [], $totals['net_total']);

        if ($discountDecision['requires_approval']) {
            $this->createDiscountApprovalRequest(
                $salesInvoice,
                $discountDecision['requested_discount_percentage'],
                $discountDecision['allowed_discount_percentage']
            );
        }

        return $salesInvoice->fresh()->load([
            'customer',
            'salesPerson',
            'items',
            'taxes',
            'fees',
            'pendingDiscountApproval',
        ]);
    });
}
 public function update(SalesInvoice $salesInvoice, array $data): SalesInvoice
{
    if ($salesInvoice->status !== 'draft') {
        throw new RuntimeException('Only draft invoices can be updated.');
    }

    return DB::transaction(function () use ($salesInvoice, $data) {
        $companyId = $salesInvoice->company_id;

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                $companyId,
                $data['posting_date'] ?? $salesInvoice->posting_date,
                'update'
            );

        $discountComesFromPreviousDocument =
            ! empty($data['sales_order_id']) ||
            ! empty($data['delivery_note_id']) ||
            ! empty($salesInvoice->sales_order_id) ||
            ! empty($salesInvoice->delivery_note_id);

        if ($discountComesFromPreviousDocument) {
            $inheritedDiscount = (float) (
                $data['discount_percentage']
                ?? $salesInvoice->discount_percentage
                ?? 0
            );

            $discountDecision = [
                'requires_approval' => false,
                'applied_discount_percentage' => $inheritedDiscount,
                'requested_discount_percentage' => $inheritedDiscount,
                'allowed_discount_percentage' => 0,
            ];
        } else {
            $discountDecision = $this->handleDiscountDecision($companyId, $data);
        }

        $data['discount_percentage'] = $discountDecision['applied_discount_percentage'];

        $totals = $this->calculateTotals($companyId, $data);
        $accounts = $this->resolvePostingAccounts($companyId, $data);

        $paidAmount = (float) ($data['paid_amount'] ?? $salesInvoice->paid_amount ?? 0);
        $outstandingAmount = (float) $totals['grand_total'] - $paidAmount;

        $salesInvoice->update(array_merge([
            'customer_id' => $data['customer_id'],
            'sales_order_id' => $data['sales_order_id'] ?? null,
            'delivery_note_id' => $data['delivery_note_id'] ?? null,
            'sales_person_id' => $data['sales_person_id'],

            'posting_date' => $data['posting_date'],
            'posting_time' => $data['posting_time'],
            'payment_due_date' => $data['payment_due_date'] ?? null,

            'posting_method' => $data['posting_method'],

            'payment_mode' => 'credit',
            'payment_account_id' => null,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => max(round($outstandingAmount, 2), 0),
            'payment_status' => $outstandingAmount <= 0
                ? 'paid'
                : ($paidAmount > 0 ? 'partially_paid' : 'unpaid'),

            'discount_percentage' => $data['discount_percentage'],
            'discount_amount' => $totals['discount_amount'],
            'net_total' => $totals['net_total'],
            'tax_total' => $totals['tax_total'],
            'fees_total' => $totals['fees_total'],
            'grand_total' => $totals['grand_total'],

            'is_asset_sale' => $data['is_asset_sale'] ?? $salesInvoice->is_asset_sale,
        ], $accounts));

        $salesInvoice->items()->delete();
        $salesInvoice->taxes()->delete();
        $salesInvoice->fees()->delete();

        $this->saveInvoiceItems($salesInvoice, $companyId, $data['items']);
        $this->saveInvoiceTaxes($salesInvoice, $companyId, $data['tax_template_ids'] ?? [], $totals['net_total']);
        $this->saveInvoiceFees($salesInvoice, $companyId, $data['fees_template_ids'] ?? [], $totals['net_total']);

        if ($discountDecision['requires_approval']) {
            $this->createDiscountApprovalRequest(
                $salesInvoice,
                $discountDecision['requested_discount_percentage'],
                $discountDecision['allowed_discount_percentage']
            );
        }

        return $salesInvoice->fresh()->load([
            'customer',
            'salesPerson',
            'items',
            'taxes',
            'fees',
            'pendingDiscountApproval',
        ]);
    });
}
 public function submit(SalesInvoice $salesInvoice): SalesInvoice
{
    if ($salesInvoice->status !== 'draft') {
        throw new RuntimeException('Only draft invoices can be submitted.');
    }

    $pendingApproval = DiscountApprovalRequest::query()
        ->where('sales_invoice_id', $salesInvoice->id)
        ->whereIn('status', [
            'pending_department_manager_approval',
            'pending_cfo_approval',
        ])
        ->exists();

    if ($pendingApproval) {
        throw new RuntimeException('Cannot submit invoice while discount approval is pending.');
    }

    $rejectedApproval = DiscountApprovalRequest::query()
        ->where('sales_invoice_id', $salesInvoice->id)
        ->where('status', 'rejected')
        ->exists();

    if ($rejectedApproval) {
        throw new RuntimeException('Cannot submit invoice because discount approval was rejected.');
    }

    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $salesInvoice->company_id,
            $salesInvoice->posting_date,
            'create'
        );

    return DB::transaction(function () use ($salesInvoice) {
        $salesInvoice->load([
            'items.item',
            'taxes',
            'fees',
            'salesPerson',
            'deliveryNote',
            'salesOrder',
        ]);

        $shouldAffectStock =
            ! $salesInvoice->delivery_note_id
            && ! $salesInvoice->is_asset_sale;

        if ($shouldAffectStock) {
            foreach ($salesInvoice->items as $item) {
                $stock = WarehouseStock::query()
                    ->where('company_id', $salesInvoice->company_id)
                    ->where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || (float) $stock->quantity < (float) $item->quantity) {
                    throw new RuntimeException("Insufficient stock for item: {$item->item_name_en}.");
                }

                $averageRate = (float) $stock->average_rate;
                $outgoingValue = (float) $item->quantity * $averageRate;

                $newQty = (float) $stock->quantity - (float) $item->quantity;
                $newValue = (float) $stock->stock_value - $outgoingValue;

                $stock->update([
                    'quantity' => $newQty,
                    'stock_value' => max($newValue, 0),
                    'average_rate' => $newQty > 0 ? $averageRate : 0,
                ]);
            }

            $this->createStockMovementEntry($salesInvoice);
        }

        $this->postJournalEntry($salesInvoice);

        $paidAmount = (float) ($salesInvoice->paid_amount ?? 0);

        $outstandingAmount =
            (float) $salesInvoice->grand_total
            - $paidAmount;

        $salesInvoice->update([
            'status' => 'submitted',
            'paid_amount' => $paidAmount,
            'outstanding_amount' => max(round($outstandingAmount, 2), 0),
            'payment_status' => $outstandingAmount <= 0
                ? 'paid'
                : ($paidAmount > 0 ? 'partially_paid' : 'unpaid'),
        ]);

        if ($salesInvoice->deliveryNote) {
            $salesInvoice->deliveryNote->update([
                'status' => 'completed',
            ]);
        }

        if ($salesInvoice->salesOrder) {
            $salesInvoice->salesOrder->update([
                'status' => 'completed',
                'sales_person_id' => $salesInvoice->sales_person_id,
            ]);
        }

        return $salesInvoice->fresh()->load([
            'customer',
            'salesPerson',
            'salesOrder',
            'deliveryNote',
            'items',
            'taxes',
            'fees',
        ]);
    });
}
  private function postJournalEntry(SalesInvoice $invoice): void
{
    app(\App\Services\FinancialYear\FinancialYearService::class)
        ->validateTransactionDate(
            $invoice->company_id,
            $invoice->posting_date,
            'create'
        );

    $journalEntry = JournalEntry::create([
        'company_id' => $invoice->company_id,
        'entry_number' => $this->generateJournalEntryNumber($invoice->company_id),
        'entry_date' => $invoice->posting_date,
        'total_debit' => 0,
        'total_credit' => 0,
        'description' => 'فاتورة مبيعات رقم - ' . $invoice->invoice_number,
        'status' => 'posted',
        'created_by' => auth('api')->id(),
    ]);

    $totalDebit = 0;
    $totalCredit = 0;

    // دائما على الذمم
    $debitAccount = $invoice->receivable_account_id;

    if (! $debitAccount) {
        throw new RuntimeException('Receivable account is missing.');
    }

    $journalEntry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $debitAccount,
        'debit' => $invoice->grand_total,
        'credit' => 0,
        'note' => 'ذمم مدينة فاتورة مبيعات ' . $invoice->invoice_number,
    ]);

    $totalDebit += $invoice->grand_total;

    $journalEntry->lines()->create([
        'company_id' => $invoice->company_id,
        'account_id' => $invoice->sales_account_id,
        'debit' => 0,
        'credit' => $invoice->net_total,
        'note' => 'إيرادات المبيعات',
    ]);

    $totalCredit += $invoice->net_total;

    foreach ($invoice->taxes as $tax) {
        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $tax->account_id,
            'debit' => 0,
            'credit' => $tax->amount,
            'note' => $tax->title,
        ]);

        $totalCredit += $tax->amount;
    }

    foreach ($invoice->fees as $fee) {
        $journalEntry->lines()->create([
            'company_id' => $invoice->company_id,
            'account_id' => $fee->account_id,
            'debit' => 0,
            'credit' => $fee->amount,
            'note' => $fee->title,
        ]);

        $totalCredit += $fee->amount;
    

  
    }

    if (! $invoice->is_asset_sale) {
        $totalCost = 0;

        foreach ($invoice->items as $item) {
            $totalCost += (float) $item->quantity * $this->getAverageRateForItem(
                $invoice->company_id,
                $item->item_id,
                $item->warehouse_id
            );
        }

        if ($totalCost > 0) {
            $journalEntry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $invoice->cogs_account_id,
                'debit' => $totalCost,
                'credit' => 0,
                'note' => 'تكلفة البضاعة المباعة',
            ]);

            $journalEntry->lines()->create([
                'company_id' => $invoice->company_id,
                'account_id' => $invoice->stock_account_id,
                'debit' => 0,
                'credit' => $totalCost,
                'note' => 'المخزون',
            ]);

            $totalDebit += $totalCost;
            $totalCredit += $totalCost;
        }
    }

    if (round($totalDebit, 2) !== round($totalCredit, 2)) {
        throw new RuntimeException('Journal entry is not balanced.');
    }

    $journalEntry->update([
        'total_debit' => $totalDebit,
        'total_credit' => $totalCredit,
    ]);

    $invoice->update([
        'journal_entry_id' => $journalEntry->id,
    ]);
}

   public function applyApprovedDiscount(
    SalesInvoice $invoice,
    float $discountPercentage
): SalesInvoice {
    if ($invoice->status !== 'draft') {
        throw new RuntimeException('Discount can only be applied to draft invoices.');
    }

    return DB::transaction(function () use ($invoice, $discountPercentage) {
        $invoice->load([
            'customer',
            'salesPerson',
            'salesOrder',
            'deliveryNote',
            'items',
            'taxes',
            'fees',
        ]);

        $data = [
            'discount_percentage' => $discountPercentage,

            'items' => $invoice->items->map(fn ($item) => [
                'sales_order_item_id' => $item->sales_order_item_id,
                'delivery_note_item_id' => $item->delivery_note_item_id,
                'item_id' => $item->item_id,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
            ])->toArray(),

            'tax_template_ids' => $invoice->taxes
                ->pluck('tax_template_id')
                ->unique()
                ->values()
                ->toArray(),

            'fees_template_ids' => $invoice->fees
                ->pluck('fees_template_id')
                ->unique()
                ->values()
                ->toArray(),
        ];

        $totals = $this->calculateTotals($invoice->company_id, $data);

        $paidAmount = (float) ($invoice->paid_amount ?? 0);
        $outstandingAmount = (float) $totals['grand_total'] - $paidAmount;

        $invoice->update([
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $totals['discount_amount'],
            'net_total' => $totals['net_total'],
            'tax_total' => $totals['tax_total'],
            'fees_total' => $totals['fees_total'],
            'grand_total' => $totals['grand_total'],
            'outstanding_amount' => max(round($outstandingAmount, 2), 0),
            'payment_status' => $outstandingAmount <= 0
                ? 'paid'
                : ($paidAmount > 0 ? 'partially_paid' : 'unpaid'),
        ]);

        $invoice->taxes()->delete();
        $invoice->fees()->delete();

        $this->saveInvoiceTaxes(
            $invoice,
            $invoice->company_id,
            $data['tax_template_ids'],
            $totals['net_total']
        );

        $this->saveInvoiceFees(
            $invoice,
            $invoice->company_id,
            $data['fees_template_ids'],
            $totals['net_total']
        );

        return $invoice->fresh([
            'customer',
            'salesPerson',
            'salesOrder',
            'deliveryNote',
            'items',
            'taxes',
            'fees',
        ]);
    });
}
public function createFromDeliveryNote(DeliveryNote $deliveryNote): SalesInvoice
{
    return DB::transaction(function () use ($deliveryNote) {
        if ($deliveryNote->status !== 'to_bill') {
            throw new RuntimeException('Only delivery notes with to_bill status can be invoiced.');
        }

        if (SalesInvoice::where('delivery_note_id', $deliveryNote->id)->exists()) {
            throw new RuntimeException('Sales invoice already exists for this delivery note.');
        }

        $deliveryNote->load([
            'salesOrder',
            'customer',
            'items.item',
            'taxes',
            'fees',
        ]);

        return $this->create([
            'sales_order_id' => $deliveryNote->sales_order_id,
            'delivery_note_id' => $deliveryNote->id,
            'customer_id' => $deliveryNote->customer_id,
            'sales_person_id' => $deliveryNote->salesOrder?->sales_person_id ?? request('sales_person_id'),

            'posting_date' => request('posting_date', now()->toDateString()),
            'posting_time' => request('posting_time', now()->format('H:i')),
            'payment_due_date' => request('payment_due_date'),

            'posting_method' => request('posting_method', 'default'),

            'receivable_account_id' => request('receivable_account_id'),
            'sales_account_id' => request('sales_account_id'),
            'cogs_account_id' => request('cogs_account_id'),
            'stock_account_id' => request('stock_account_id'),

            'payment_mode' => 'credit',
            'payment_account_id' => null,

            'discount_percentage' => (float) $deliveryNote->discount_percentage,
            'is_asset_sale' => false,

            'items' => $deliveryNote->items->map(fn ($item) => [
                'delivery_note_item_id' => $item->id,
                'item_id' => $item->item_id,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
            ])->toArray(),

            'tax_template_ids' => $deliveryNote->taxes
                ->pluck('tax_template_id')
                ->unique()
                ->values()
                ->toArray(),

            'fees_template_ids' => $deliveryNote->fees
                ->pluck('fees_template_id')
                ->unique()
                ->values()
                ->toArray(),
        ]);
    });
}    public function delete(SalesInvoice $salesInvoice): void
    {
        if ($salesInvoice->status !== 'draft') {
            throw new RuntimeException('Only draft invoices can be deleted.');
        }

        DB::transaction(function () use ($salesInvoice) {
            $salesInvoice->discountApprovalRequests()->delete();
            $salesInvoice->items()->delete();
            $salesInvoice->taxes()->delete();
            $salesInvoice->fees()->delete();
            $salesInvoice->delete();
        });
    }

    private function handleDiscountDecision(int $companyId, array $data): array
    {
        $requestedDiscount = (float) ($data['discount_percentage'] ?? 0);

        if ($requestedDiscount <= 0) {
            return [
                'requires_approval' => false,
                'applied_discount_percentage' => 0,
                'requested_discount_percentage' => 0,
                'allowed_discount_percentage' => 0,
            ];
        }

        $user = auth('api')->user();

        if (! $user) {
            throw new RuntimeException('Unauthenticated user.');
        }

        $settings = DiscountSetting::query()->where('company_id', $companyId)->first();

        if (! $settings) {
            throw new RuntimeException('Discount settings are not configured.');
        }

        $allowedDiscount = $this->getAllowedDiscountForUser($user, $settings);

        if ($requestedDiscount <= $allowedDiscount) {
            return [
                'requires_approval' => false,
                'applied_discount_percentage' => $requestedDiscount,
                'requested_discount_percentage' => $requestedDiscount,
                'allowed_discount_percentage' => $allowedDiscount,
            ];
        }

        return [
            'requires_approval' => true,
            'applied_discount_percentage' => 0,
            'requested_discount_percentage' => $requestedDiscount,
            'allowed_discount_percentage' => $allowedDiscount,
        ];
    }

  private function getAllowedDiscountForUser($user, DiscountSetting $settings): float
{
    if ($user->hasRole('Accountant Sub') || $user->hasRole('Sub Accountant')) {
        return (float) $settings->sub_accountant_max_discount;
    }

    if ($user->hasRole('Accountant Chief') || $user->hasRole('Department Manager')) {
        return (float) $settings->department_manager_max_discount;
    }

    if ($user->hasRole('CFO')) {
        return 100;
    }

    return 0;
}   private function createDiscountApprovalRequest(
    SalesInvoice $invoice,
    float $requestedDiscount,
    float $allowedDiscount
): void {
    $settings = DiscountSetting::query()
        ->where('company_id', $invoice->company_id)
        ->firstOrFail();

    $user = auth('api')->user();

    $approvalLevel = $requestedDiscount <= (float) $settings->department_manager_max_discount
        ? 'department_manager'
        : 'cfo';

    if ($user->hasRole('Accountant Chief')) {
        $status = 'pending_cfo_approval';
        $approvers = User::role('CFO')->get();
    } else {
        $status = 'pending_department_manager_approval';
        $approvers = User::role('Accountant Chief')->get();
    }

    $approvalRequest = DiscountApprovalRequest::query()
        ->where('sales_invoice_id', $invoice->id)
        ->whereIn('status', [
            'pending_department_manager_approval',
            'pending_cfo_approval',
        ])
        ->first();

    if ($approvalRequest) {
        $approvalRequest->update([
            'sales_order_id' => null,
            'requested_discount_percentage' => $requestedDiscount,
            'allowed_discount_percentage' => $allowedDiscount,
            'approval_level' => $approvalLevel,
            'status' => $status,
            'rejection_reason' => null,
            'approved_by' => null,
            'responded_at' => null,
            'forwarded_by' => null,
            'forwarded_at' => null,
        ]);

        $approvalRequest = $approvalRequest->fresh(['invoice', 'requester']);
    } else {
        $approvalRequest = DiscountApprovalRequest::query()->create([
            'company_id' => $invoice->company_id,
            'sales_order_id' => null,
            'sales_invoice_id' => $invoice->id,
            'requested_by' => auth('api')->id(),
            'requested_discount_percentage' => $requestedDiscount,
            'allowed_discount_percentage' => $allowedDiscount,
            'approval_level' => $approvalLevel,
            'status' => $status,
        ])->load(['invoice', 'requester']);
    }

    foreach ($approvers as $approver) {
        $approver->notify(
            new DiscountApprovalRequestedNotification($approvalRequest)
        );
    }
}
 private function resolvePostingAccounts(int $companyId, array $data): array
{
    if (($data['posting_method'] ?? 'default') === 'manual') {
        if (
            empty($data['receivable_account_id']) ||
            empty($data['sales_account_id']) ||
            empty($data['cogs_account_id']) ||
            empty($data['stock_account_id'])
        ) {
            throw new RuntimeException('Manual posting accounts are required.');
        }

        return [
            'receivable_account_id' => $data['receivable_account_id'],
            'sales_account_id' => $data['sales_account_id'],
            'cogs_account_id' => $data['cogs_account_id'],
            'stock_account_id' => $data['stock_account_id'],
        ];
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (! $settings) {
        throw new RuntimeException('Company account settings are not configured.');
    }

    if (
        ! $settings->default_receivable_account_id ||
        ! $settings->default_direct_income_account_id ||
        ! $settings->default_cogs_account_id ||
        ! $settings->default_inventory_account_id
    ) {
        throw new RuntimeException('Default posting accounts are not configured in Company Settings.');
    }

    return [
        'receivable_account_id' => $settings->default_receivable_account_id,
        'sales_account_id' => $settings->default_direct_income_account_id,
        'cogs_account_id' => $settings->default_cogs_account_id,
        'stock_account_id' => $settings->default_inventory_account_id,
    ];
}
    private function calculateTotals(int $companyId, array $data): array
{
    $itemTotal = 0;

    foreach ($data['items'] as $row) {
        $item = Item::query()->findOrFail($row['item_id']);

        $rate = $this->resolveItemRate($row, $item);

        $qty = (float) $row['quantity'];

        $itemTotal += $qty * $rate;
    }

    $itemTotal = round($itemTotal, 2);

    $discountPercentage = (float) ($data['discount_percentage'] ?? 0);

    $discountAmount = round($itemTotal * ($discountPercentage / 100), 2);

    $netTotal = round($itemTotal - $discountAmount, 2);

    $taxTotal = 0;

    if (! empty($data['tax_template_ids'])) {
        $templates = TaxTemplate::with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $data['tax_template_ids'])
            ->get();

        foreach ($templates as $template) {
            foreach ($template->lines as $line) {
                $taxTotal += $line->type === 'on_net_total'
                    ? $netTotal * ((float) $line->tax_rate / 100)
                    : (float) ($line->amount ?? 0);
            }
        }
    }

    $taxTotal = round($taxTotal, 2);

    $feesTotal = 0;

    if (! empty($data['fees_template_ids'])) {
        $templates = FeesTemplate::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $data['fees_template_ids'])
            ->get();

        foreach ($templates as $template) {
            $feesTotal += $template->type === 'percentage'
                ? $netTotal * ((float) $template->fees_rate / 100)
                : (float) ($template->amount ?? 0);
        }
    }

    $feesTotal = round($feesTotal, 2);

    $grandTotal = round($netTotal + $taxTotal + $feesTotal, 2);

    return [
        'item_total' => $itemTotal,
        'net_total' => $netTotal,
        'tax_total' => $taxTotal,
        'fees_total' => $feesTotal,
        'discount_percentage' => $discountPercentage,
        'discount_amount' => $discountAmount,
        'grand_total' => $grandTotal,
    ];
}
private function resolveItemRate(array $row, Item $item): float
{
    $rate = $row['rate']
        ?? $item->selling_price
        ?? $item->sale_price
        ?? $item->standard_rate
        ?? 0;

    $rate = (float) $rate;

    if ($rate <= 0) {
        throw new RuntimeException(
            "Selling price is not configured for item: {$item->name_en}."
        );
    }

    return $rate;
}
    private function saveInvoiceItems(SalesInvoice $invoice, int $companyId, array $items): void
    {
        foreach ($items as $row) {
            $item = Item::query()->findOrFail($row['item_id']);

          $rate = $this->resolveItemRate($row, $item);
            if ($rate <= 0) {
                throw new RuntimeException("Selling price is not configured for item: {$item->name_en}.");
            }

            $invoice->items()->create([
                'sales_order_item_id' => $row['sales_order_item_id'] ?? null,
                'delivery_note_item_id' => $row['delivery_note_item_id'] ?? null,
                'item_id' => $row['item_id'],
                'warehouse_id' => $row['warehouse_id'],
                'item_code' => $item->item_code,
                'item_name_ar' => $item->name_ar,
                'item_name_en' => $item->name_en,
                'quantity' => $row['quantity'],
                'rate' => $rate,
                'amount' => $row['quantity'] * $rate,
            ]);
        }
    }

    private function saveInvoiceTaxes(SalesInvoice $invoice, int $companyId, array $ids, float $netTotal): void
    {
        if (empty($ids)) {
            return;
        }

        $templates = TaxTemplate::with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($templates as $template) {
            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? $netTotal * (((float) $line->tax_rate) / 100)
                    : ((float) ($line->amount ?? 0));

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
        if (empty($ids)) {
            return;
        }

        $templates = FeesTemplate::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($templates as $template) {
            $amount = $template->type === 'percentage'
                ? $netTotal * (((float) $template->fees_rate) / 100)
                : ((float) ($template->amount ?? 0));

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
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $invoice->company_id,
        $invoice->posting_date,
        'create'
    );
        $stockEntry = StockEntry::query()->create([
            'company_id' => $invoice->company_id,
            'series' => 'SINV-ST-' . now()->format('Y') . '-' . rand(1000, 9999),
            'entry_type' => 'material_issue',
            'posting_date' => $invoice->posting_date,
            'posting_time' => $invoice->posting_time,
            'total_incoming_value' => 0,
            'total_outgoing_value' => 0,
            'value_difference' => 0,
            'status' => 'submitted',
            'created_by' => auth('api')->id(),
        ]);

        $totalOutgoing = 0;

        foreach ($invoice->items as $item) {
            $stock = WarehouseStock::query()
                ->where('company_id', $invoice->company_id)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->warehouse_id)
                ->first();

            $basicRate = (float) ($stock?->average_rate ?? 0);
            $outgoingValue = (float) $item->quantity * $basicRate;
            $totalOutgoing += $outgoingValue;

            $stockEntry->items()->create([
                'item_id' => $item->item_id,
                'barcode' => $item->item?->barcode ?? null,
                'source_warehouse_id' => $item->warehouse_id,
                'target_warehouse_id' => null,
                'quantity' => $item->quantity,
                'basic_rate' => $basicRate,
                'incoming_value' => 0,
                'outgoing_value' => $outgoingValue,
                'value_difference' => -$outgoingValue,
            ]);
        }

        $stockEntry->update([
            'total_outgoing_value' => $totalOutgoing,
            'value_difference' => -$totalOutgoing,
        ]);
    }

    private function getAverageRateForItem(int $companyId, int $itemId, int $warehouseId): float
    {
        $stock = WarehouseStock::query()
            ->where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return (float) ($stock?->average_rate ?? 0);
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $count = SalesInvoice::query()
            ->where('company_id', $companyId)
            ->withTrashed()
            ->count() + 1;

        return 'INV-' . now()->format('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $lastEntry = JournalEntry::query()
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->first();

        if (! $lastEntry) {
            return 'JV-' . now()->year . '-00001';
        }

        $parts = explode('-', $lastEntry->entry_number);
        $nextNumber = ((int) end($parts)) + 1;

        return 'JV-' . now()->year . '-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}