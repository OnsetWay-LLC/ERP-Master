<?php

namespace App\Services\AssetRepair;

use App\Models\Asset;
use App\Models\AssetRepair;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetRepairService
{
    private array $relations = [
        'asset.assetItem',
        'asset.assetCategory',
        'asset.location',
        'items.purchaseInvoice',
        'items.expenseAccount',
        'items.paymentAccount',
        'journalEntry.lines.account',
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetRepair::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetRepair
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $asset = $this->getValidAsset($companyId, (int) $data['asset_id']);

            $repair = AssetRepair::query()->create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),
                'asset_id' => $asset->id,
                'repair_status' => $data['repair_status'],
                'failure_date' => $data['failure_date'],
                'completed_date' => null,
                'error_description' => $data['error_description'] ?? null,
                'actions_performed' => $data['actions_performed'] ?? null,
                'repair_cost_total' => 0,
                'status' => 'draft',
                'created_by' => $createdBy,
            ]);

            if (! empty($data['items'])) {
                $itemsData = $this->prepareItems($companyId, $data['items']);

                foreach ($itemsData as $item) {
                    $repair->items()->create($item);
                }

                $repair->update([
                    'repair_cost_total' => collect($itemsData)->sum('repair_cost'),
                ]);
            }

            return $repair->fresh($this->relations);
        });
    }

    public function update(AssetRepair $repair, array $data): AssetRepair
    {
        return DB::transaction(function () use ($repair, $data) {
            if ($repair->status !== 'draft') {
                throw new InvalidArgumentException('Submitted asset repair cannot be updated.');
            }

            $assetId = $data['asset_id'] ?? $repair->asset_id;
            $asset = $this->getValidAsset($repair->company_id, (int) $assetId);

            $updateData = [
                'asset_id' => $asset->id,
                'repair_status' => $data['repair_status'] ?? $repair->repair_status,
                'failure_date' => $data['failure_date'] ?? $repair->failure_date?->format('Y-m-d'),
                'error_description' => $data['error_description'] ?? $repair->error_description,
                'actions_performed' => $data['actions_performed'] ?? $repair->actions_performed,
                'completed_date' => null,
            ];

            if (isset($data['items'])) {
                $itemsData = $this->prepareItems($repair->company_id, $data['items']);

                $repair->items()->delete();

                foreach ($itemsData as $item) {
                    $repair->items()->create($item);
                }

                $updateData['repair_cost_total'] = collect($itemsData)->sum('repair_cost');
            }

            $repair->update($updateData);

            return $repair->fresh($this->relations);
        });
    }

    public function submit(AssetRepair $repair, ?int $submittedBy = null): AssetRepair
    {
        return DB::transaction(function () use ($repair, $submittedBy) {
            if ($repair->status !== 'draft') {
                throw new InvalidArgumentException('Only draft asset repair can be submitted.');
            }

            $repair->load($this->relations);

            if ($repair->repair_status === 'pending') {
                throw new InvalidArgumentException('Pending repair status can only be saved as draft, not submitted.');
            }

            if (! in_array($repair->repair_status, ['completed', 'cancelled'], true)) {
                throw new InvalidArgumentException('Repair status must be completed or cancelled before submit.');
            }

            if ($repair->items->isEmpty()) {
                throw new InvalidArgumentException('Repair purchase invoice table must contain at least one row.');
            }

            foreach ($repair->items as $item) {
                if ((float) $item->repair_cost <= 0) {
                    throw new InvalidArgumentException('Repair cost must be greater than zero.');
                }
            }

            $journalEntry = $this->createRepairJournalEntry(
                repair: $repair,
                userId: $submittedBy
            );

            $repair->update([
                'status' => 'submitted',
                'completed_date' => $repair->repair_status === 'completed' ? now() : null,
                'journal_entry_id' => $journalEntry->id,
                'submitted_at' => now(),
                'submitted_by' => $submittedBy,
            ]);

            return $repair->fresh($this->relations);
        });
    }

    public function delete(AssetRepair $repair): void
    {
        if ($repair->status !== 'draft') {
            throw new InvalidArgumentException('Submitted asset repair cannot be deleted.');
        }

        $repair->items()->delete();
        $repair->delete();
    }

    public function availableAssets(int $companyId): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->with(['assetItem', 'assetCategory', 'location'])
            ->latest('id')
            ->get();
    }

    public function availablePurchaseInvoices(int $companyId): Collection
    {
        return DB::table('purchase_invoices')
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->orderByDesc('id')
            ->get();
    }

    private function prepareItems(int $companyId, array $items): array
    {
        $result = [];
        $usedInvoiceIds = [];

        foreach ($items as $row) {
            $invoiceId = (int) $row['purchase_invoice_id'];

            if (in_array($invoiceId, $usedInvoiceIds, true)) {
                throw new InvalidArgumentException('Same purchase invoice cannot be selected more than once.');
            }

            $usedInvoiceIds[] = $invoiceId;

            $invoiceInfo = $this->resolvePurchaseInvoiceAccounts($companyId, $invoiceId);

            $result[] = [
                'purchase_invoice_id' => $invoiceId,
                'expense_account_id' => $invoiceInfo['expense_account_id'],
                'payment_account_id' => $invoiceInfo['payment_account_id'],
                'payment_mode' => $invoiceInfo['payment_mode'],
                'repair_cost' => round((float) $row['repair_cost'], 2),
            ];
        }

        return $result;
    }

    private function getValidAsset(int $companyId, int $assetId): Asset
    {
        $asset = Asset::query()
            ->where('company_id', $companyId)
            ->where('id', $assetId)
            ->where('status', 'submitted')
            ->with(['assetItem', 'assetCategory', 'location'])
            ->first();

        if (! $asset) {
            throw new InvalidArgumentException('Invalid submitted asset selected.');
        }

        return $asset;
    }

    private function resolvePurchaseInvoiceAccounts(int $companyId, int $purchaseInvoiceId): array
    {
        $invoice = DB::table('purchase_invoices')
            ->where('company_id', $companyId)
            ->where('id', $purchaseInvoiceId)
            ->where('status', 'submitted')
            ->first();

        if (! $invoice) {
            throw new InvalidArgumentException('Invalid submitted purchase invoice selected.');
        }

        $invoiceData = (array) $invoice;

        $expenseAccountId =
            $invoiceData['purchase_account_id']
            ?? $invoiceData['expense_account_id']
            ?? null;

        if (! $expenseAccountId) {
            throw new InvalidArgumentException('Expense account is not configured on purchase invoice.');
        }

        $paymentMode = $invoiceData['payment_mode'] ?? 'credit';

        $paymentAccountId = match ($paymentMode) {
            'cash' => $invoiceData['cash_account_id'] ?? null,
            'bank' => $invoiceData['bank_account_id'] ?? null,
            default => $invoiceData['supplier_payable_account_id']
                ?? $invoiceData['payable_account_id']
                ?? null,
        };

        if (! $paymentAccountId) {
            throw new InvalidArgumentException('Payment account is not configured on purchase invoice.');
        }

        return [
            'expense_account_id' => (int) $expenseAccountId,
            'payment_account_id' => (int) $paymentAccountId,
            'payment_mode' => $paymentMode,
        ];
    }

    private function createRepairJournalEntry(AssetRepair $repair, ?int $userId): JournalEntry
    {
        $repair->load(['asset', 'items']);

        $entry = JournalEntry::query()->create([
            'company_id' => $repair->company_id,
            'entry_number' => $this->generateJournalEntryNumber($repair->company_id),
            'entry_date' => now()->toDateString(),
            'description' => 'Asset repair - ' . $repair->asset?->asset_name_en,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $userId,
            'total_debit' => $repair->repair_cost_total,
            'total_credit' => $repair->repair_cost_total,
        ]);

        foreach ($repair->items as $item) {
            $amount = (float) $item->repair_cost;

            if ($repair->repair_status === 'completed') {
                $debitAccountId = $item->expense_account_id;
                $creditAccountId = $item->payment_account_id;
                $debitNote = 'Maintenance expense';
                $creditNote = 'Repair invoice payment account';
            } else {
                $debitAccountId = $item->payment_account_id;
                $creditAccountId = $item->expense_account_id;
                $debitNote = 'Reverse repair payment account';
                $creditNote = 'Reverse maintenance expense';
            }

            $debitLine = JournalEntryLine::query()->create([
                'company_id' => $repair->company_id,
                'journal_entry_id' => $entry->id,
                'account_id' => $debitAccountId,
                'debit' => $amount,
                'credit' => 0,
                'note' => $debitNote,
            ]);

            $creditLine = JournalEntryLine::query()->create([
                'company_id' => $repair->company_id,
                'journal_entry_id' => $entry->id,
                'account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $amount,
                'note' => $creditNote,
            ]);

            $this->createLedgerLine($repair->company_id, $entry, $debitLine, $userId);
            $this->createLedgerLine($repair->company_id, $entry, $creditLine, $userId);
        }

        return $entry->fresh(['lines.account']);
    }

    private function createLedgerLine(
        int $companyId,
        JournalEntry $entry,
        JournalEntryLine $line,
        ?int $userId
    ): void {
        $lastBalance = GeneralLedger::query()
            ->where('company_id', $companyId)
            ->where('account_id', $line->account_id)
            ->latest('id')
            ->value('balance') ?? 0;

        $newBalance = ((float) $lastBalance + (float) $line->debit) - (float) $line->credit;

        GeneralLedger::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'journal_entry_line_id' => $line->id,
            'account_id' => $line->account_id,
            'entry_date' => $entry->entry_date,
            'debit' => $line->debit,
            'credit' => $line->credit,
            'balance' => $newBalance,
            'description' => $entry->description,
            'created_by' => $userId,
        ]);
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = AssetRepair::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'ARPR-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}