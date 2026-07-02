<?php

namespace App\Services\AssetRepair;

use App\Models\Asset;
use App\Models\AssetRepair;
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
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetRepair::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $userId): AssetRepair
{
    return DB::transaction(function () use ($data, $companyId, $userId) {
        $items = $this->prepareItems($companyId, $data['items'] ?? []);
        unset($data['items']);

        $this->getValidAsset($companyId, (int) $data['asset_id']);

        $data['company_id'] = $companyId;
        $data['created_by'] = $userId;
        $data['series'] = $this->generateSeries($companyId);
        $data['status'] = 'draft';
        $data['repair_status'] = 'pending';
        $data['repair_cost_total'] = collect($items)->sum('repair_cost');

        $repair = AssetRepair::create($data);

        foreach ($items as $item) {
            $repair->items()->create($item);
        }

        return $repair->fresh($this->relations);
    });
}
private function generateSeries(int $companyId): string
{
    $year = now()->format('Y');

    $lastRepair = AssetRepair::where('company_id', $companyId)
        ->where('series', 'like', "AR-{$year}-%")
        ->orderByDesc('id')
        ->lockForUpdate()
        ->first();

    $nextNumber = 1;

    if ($lastRepair) {
        $lastNumber = (int) substr($lastRepair->series, -5);
        $nextNumber = $lastNumber + 1;
    }

    return 'AR-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}
 public function update(AssetRepair $assetRepair, array $data): AssetRepair
{
    if ($assetRepair->status !== 'draft') {
        throw new InvalidArgumentException('Only draft asset repairs can be updated.');
    }

    return DB::transaction(function () use ($assetRepair, $data) {
        $companyId = (int) $assetRepair->company_id;

        $hasItems = array_key_exists('items', $data);
        $items = $hasItems
            ? $this->prepareItems($companyId, $data['items'] ?? [])
            : null;

        unset($data['items'], $data['status'], $data['repair_cost_total']);

        if (isset($data['asset_id'])) {
            $this->getValidAsset($companyId, (int) $data['asset_id']);
        }

        if (isset($data['repair_status']) && $data['repair_status'] !== 'completed') {
            throw new InvalidArgumentException('Repair status can only be changed to completed.');
        }

        $assetRepair->update($data);

        if ($hasItems) {
            $assetRepair->items()->delete();

            foreach ($items as $item) {
                $assetRepair->items()->create($item);
            }

            $assetRepair->update([
                'repair_cost_total' => collect($items)->sum('repair_cost'),
            ]);
        }

        return $assetRepair->fresh($this->relations);
    });

}   public function submit(AssetRepair $assetRepair, ?int $userId): AssetRepair
{
    if ($assetRepair->status !== 'draft') {
        throw new InvalidArgumentException('Only draft asset repairs can be submitted.');
    }

    if ($assetRepair->repair_status !== 'completed') {
        throw new InvalidArgumentException('Repair must be completed before submission.');
    }

    return DB::transaction(function () use ($assetRepair, $userId) {
        $assetRepair->load('items');

        if ($assetRepair->items->isEmpty()) {
            throw new InvalidArgumentException('Asset repair must have at least one item.');
        }

        if ((float) $assetRepair->repair_cost_total <= 0) {
            throw new InvalidArgumentException('Repair cost total must be greater than zero.');
        }

        // هون خلي كود إنشاء Journal Entry الموجود عندك
        // $journalEntry = $this->createJournalEntry($assetRepair, $userId);

        $assetRepair->update([
            'status' => 'submitted',
            'repair_status' => 'completed',
            'submitted_at' => now(),
            'submitted_by' => $userId,

            // إذا عندك journal entry:
            // 'journal_entry_id' => $journalEntry->id,
        ]);

        return $assetRepair->fresh([
            'asset.assetItem',
            'asset.assetCategory',
            'asset.location',
            'items.purchaseInvoice',
            'items.expenseAccount',
            'items.paymentAccount',
            'journalEntry.lines.account',
        ]);
    });
}

   public function cancel(AssetRepair $assetRepair): AssetRepair
{
    if ($assetRepair->status !== 'submitted') {
        throw new InvalidArgumentException('Only submitted asset repairs can be cancelled.');
    }

    return DB::transaction(function () use ($assetRepair) {
       

        $assetRepair->update([
            'status' => 'cancelled',
            'repair_status' => 'cancelled',
        ]);

        return $assetRepair->fresh([
            'asset.assetItem',
            'asset.assetCategory',
            'asset.location',
            'items.purchaseInvoice',
            'items.expenseAccount',
            'items.paymentAccount',
            'journalEntry.lines.account',
        ]);
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
                'repair_cost' => round((float) $invoiceInfo['grand_total'], 2),
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

        if (!$asset) {
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
            ->whereIn('invoice_type', ['from_receipt', 'manual_inventory'])
            ->first();

        if (!$invoice) {
            throw new InvalidArgumentException('Invalid submitted purchase invoice selected.');
        }

        $invoiceData = (array) $invoice;

        $expenseAccountId = $invoiceData['purchase_account_id'] ?? null;

        if (!$expenseAccountId) {
            throw new InvalidArgumentException('Expense account is not configured on purchase invoice.');
        }

        $paymentMode = $invoiceData['payment_mode'] ?? 'credit';

        $paymentAccountId = match ($paymentMode) {
            'cash' => $invoiceData['cash_account_id'] ?? null,
            'bank' => $invoiceData['bank_account_id'] ?? null,
            default => $invoiceData['supplier_payable_account_id'] ?? null,
        };

        if (!$paymentAccountId) {
            throw new InvalidArgumentException('Payment account is not configured on purchase invoice.');
        }

        return [
            'expense_account_id' => (int) $expenseAccountId,
            'payment_account_id' => (int) $paymentAccountId,
            'payment_mode' => $paymentMode,
            'grand_total' => (float) $invoiceData['grand_total'],
        ];
    }

 
}