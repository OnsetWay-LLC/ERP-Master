<?php

namespace App\Services\AssetValueAdjustment;

use App\Models\Asset;
use App\Models\AssetValueAdjustment;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetValueAdjustmentService
{
    private array $relations = [
        'asset.assetItem',
        'asset.assetCategory',
        'asset.location',
        'assetCategory',
        'differenceAccount',
        'journalEntry.lines.account',
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetValueAdjustment::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetValueAdjustment
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['posting_date'],
        'create'
    );
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $asset = $this->getValidAsset($companyId, (int) $data['asset_id']);

            $this->validatePostingDate($asset, $data['posting_date']);

            $differenceAccount = $this->getValidDifferenceAccount(
                $companyId,
                (int) $data['difference_account_id']
            );

            $currentAssetValue = $this->calculateCurrentAssetValue($asset);
            $newAssetValue = round((float) $data['new_asset_value'], 2);
            $differenceAmount = round($newAssetValue - $currentAssetValue, 2);

            if ($differenceAmount == 0.0) {
                throw new InvalidArgumentException('Difference amount must not be zero.');
            }

            return AssetValueAdjustment::query()->create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),
                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,
                'posting_date' => $data['posting_date'],
                'finance_book' => $asset->assetCategory?->finance_book,
                'current_asset_value' => $currentAssetValue,
                'new_asset_value' => $newAssetValue,
                'difference_amount' => $differenceAmount,
                'difference_account_id' => $differenceAccount->id,
                'status' => 'draft',
                'created_by' => $createdBy,
            ])->fresh($this->relations);
        });
    }

    public function update(AssetValueAdjustment $adjustment, array $data): AssetValueAdjustment
    {
        return DB::transaction(function () use ($adjustment, $data) {
            if ($adjustment->status !== 'draft') {
                throw new InvalidArgumentException('Submitted asset value adjustment cannot be updated.');
            }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $adjustment->company_id,
        $data['posting_date'] ?? $adjustment->posting_date,
        'update'
    );
            $assetId = $data['asset_id'] ?? $adjustment->asset_id;

            $asset = $this->getValidAsset(
                $adjustment->company_id,
                (int) $assetId
            );

            $postingDate = $data['posting_date'] ?? $adjustment->posting_date?->format('Y-m-d');

            $this->validatePostingDate($asset, $postingDate);

            $differenceAccountId = $data['difference_account_id'] ?? $adjustment->difference_account_id;

            $differenceAccount = $this->getValidDifferenceAccount(
                $adjustment->company_id,
                (int) $differenceAccountId
            );

            $currentAssetValue = $this->calculateCurrentAssetValue($asset);
            $newAssetValue = isset($data['new_asset_value'])
                ? round((float) $data['new_asset_value'], 2)
                : (float) $adjustment->new_asset_value;

            $differenceAmount = round($newAssetValue - $currentAssetValue, 2);

            if ($differenceAmount == 0.0) {
                throw new InvalidArgumentException('Difference amount must not be zero.');
            }

            $adjustment->update([
                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,
                'posting_date' => $postingDate,
                'finance_book' => $asset->assetCategory?->finance_book,
                'current_asset_value' => $currentAssetValue,
                'new_asset_value' => $newAssetValue,
                'difference_amount' => $differenceAmount,
                'difference_account_id' => $differenceAccount->id,
            ]);

            return $adjustment->fresh($this->relations);
        });
    }

    public function submit(AssetValueAdjustment $adjustment, ?int $submittedBy = null): AssetValueAdjustment
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $adjustment->company_id,
        $adjustment->posting_date,
        'create'
    );
        return DB::transaction(function () use ($adjustment, $submittedBy) {
            if ($adjustment->status !== 'draft') {
                throw new InvalidArgumentException('Only draft asset value adjustment can be submitted.');
            }

            $adjustment->load(['asset.assetCategory', 'differenceAccount']);

            $asset = $this->getValidAsset($adjustment->company_id, $adjustment->asset_id);

            $this->validatePostingDate($asset, $adjustment->posting_date?->format('Y-m-d'));

            $currentAssetValue = $this->calculateCurrentAssetValue($asset);
            $newAssetValue = (float) $adjustment->new_asset_value;
            $differenceAmount = round($newAssetValue - $currentAssetValue, 2);

            if ($differenceAmount == 0.0) {
                throw new InvalidArgumentException('Difference amount must not be zero.');
            }

            $fixedAssetAccountId = $asset->assetCategory?->fixed_asset_account_id;

            if (! $fixedAssetAccountId) {
                throw new InvalidArgumentException('Fixed asset account is not configured on asset category.');
            }

            $journalEntry = $this->createPostedJournalEntry(
                companyId: $adjustment->company_id,
                userId: $submittedBy,
                entryDate: $adjustment->posting_date?->format('Y-m-d'),
                assetName: $asset->asset_name_en,
                fixedAssetAccountId: (int) $fixedAssetAccountId,
                differenceAccountId: (int) $adjustment->difference_account_id,
                differenceAmount: $differenceAmount
            );

            $openingAccumulatedDepreciation = (float) ($asset->opening_accumulated_depreciation ?? 0);

            $asset->update([
                'net_purchase_amount' => round($newAssetValue + $openingAccumulatedDepreciation, 2),
            ]);

            $adjustment->update([
                'current_asset_value' => $currentAssetValue,
                'difference_amount' => $differenceAmount,
                'journal_entry_id' => $journalEntry->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => $submittedBy,
            ]);

            $this->rebuildFutureDepreciationSchedule($asset->fresh(), $adjustment);

            return $adjustment->fresh($this->relations);
        });
    }

    public function delete(AssetValueAdjustment $adjustment): void
    {
        if ($adjustment->status !== 'draft') {
            throw new InvalidArgumentException('Submitted asset value adjustment cannot be deleted.');
        }

        $adjustment->delete();
    }

    public function availableAssets(int $companyId): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereNotIn('status', ['sold', 'scrapped', 'draft', 'capitalized'])
            ->with(['assetItem', 'assetCategory', 'location'])
            ->latest('id')
            ->get();
    }

    public function differenceAccounts(int $companyId): Collection
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->orderBy('account_number')
            ->get();
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

    private function getValidDifferenceAccount(int $companyId, int $accountId): ChartOfAccount
    {
        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->first();

        if (! $account) {
            throw new InvalidArgumentException('Invalid difference account selected.');
        }

        return $account;
    }

    private function validatePostingDate(Asset $asset, string $postingDate): void
    {
        $purchaseDate = $asset->purchase_date?->format('Y-m-d');

        if ($purchaseDate && $postingDate < $purchaseDate) {
            throw new InvalidArgumentException('Adjustment date must be greater than or equal to asset purchase date.');
        }
    }

    private function calculateCurrentAssetValue(Asset $asset): float
    {
        $netPurchaseAmount = (float) $asset->net_purchase_amount;
        $openingAccumulatedDepreciation = (float) ($asset->opening_accumulated_depreciation ?? 0);

        return round(max($netPurchaseAmount - $openingAccumulatedDepreciation, 0), 2);
    }

    private function createPostedJournalEntry(
        int $companyId,
        ?int $userId,
        string $entryDate,
        string $assetName,
        int $fixedAssetAccountId,
        int $differenceAccountId,
        float $differenceAmount
    ): JournalEntry {
        $amount = abs($differenceAmount);

        $entry = JournalEntry::query()->create([
            'company_id' => $companyId,
            'entry_number' => $this->generateJournalEntryNumber($companyId),
            'entry_date' => $entryDate,
            'description' => 'Asset value adjustment - ' . $assetName,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $userId,
            'total_debit' => $amount,
            'total_credit' => $amount,
        ]);

        if ($differenceAmount > 0) {
            $debitAccountId = $fixedAssetAccountId;
            $creditAccountId = $differenceAccountId;
            $debitNote = 'Increase fixed asset value';
            $creditNote = 'Asset value adjustment difference';
        } else {
            $debitAccountId = $differenceAccountId;
            $creditAccountId = $fixedAssetAccountId;
            $debitNote = 'Asset value adjustment decrease';
            $creditNote = 'Decrease fixed asset value';
        }

        $debitLine = JournalEntryLine::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'account_id' => $debitAccountId,
            'debit' => $amount,
            'credit' => 0,
            'note' => $debitNote,
        ]);

        $creditLine = JournalEntryLine::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'account_id' => $creditAccountId,
            'debit' => 0,
            'credit' => $amount,
            'note' => $creditNote,
        ]);

        $this->createLedgerLine($companyId, $entry, $debitLine, $userId);
        $this->createLedgerLine($companyId, $entry, $creditLine, $userId);

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

    private function rebuildFutureDepreciationSchedule(Asset $asset, AssetValueAdjustment $adjustment): void
    {
        // اربطي هنا جدول الإهلاك عندك إذا موجود.
        // الفكرة:
        // 1) حذف/إلغاء أقساط الإهلاك المستقبلية غير المرحلة بعد تاريخ التعديل.
        // 2) إعادة إنشائها بناءً على new_asset_value.
        // حالياً تركناها Hook حتى لا نكسر المشروع إذا جدول depreciation schedules غير موجود.
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = AssetValueAdjustment::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'AVA-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}