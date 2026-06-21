<?php

namespace App\Services\AssetScrapping;

use App\Models\Asset;
use App\Models\AssetScrapping;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\CompanyAccountSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetScrappingService
{
    private array $relations = [
        'asset.assetItem',
        'asset.assetCategory',
        'asset.location',
        'assetCategory',
        'fixedAssetAccount',
        'accumulatedDepreciationAccount',
        'lossOnDisposalAccount',
        'journalEntry.lines.account',
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetScrapping::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetScrapping
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['scrap_date'],
        'create'
    );
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $asset = $this->getValidAsset($companyId, (int) $data['asset_id']);

            $this->validateScrapDate($asset, $data['scrap_date']);

            [$fixedAssetAccountId, $accumulatedAccountId, $lossAccountId] =
                $this->resolveAccounts($asset);

            $assetCost = round((float) $asset->net_purchase_amount, 2);
            $accumulated = round((float) ($asset->opening_accumulated_depreciation ?? 0), 2);
            $bookValueLoss = round(max($assetCost - $accumulated, 0), 2);

            return AssetScrapping::query()->create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),

                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,

                'scrap_date' => $data['scrap_date'],

                'asset_cost' => $assetCost,
                'accumulated_depreciation_amount' => $accumulated,
                'book_value_loss' => $bookValueLoss,

                'fixed_asset_account_id' => $fixedAssetAccountId,
                'accumulated_depreciation_account_id' => $accumulatedAccountId,
                'loss_on_disposal_account_id' => $lossAccountId,

                'status' => 'draft',
                'created_by' => $createdBy,
            ])->fresh($this->relations);
        });
    }

    public function update(AssetScrapping $scrapping, array $data): AssetScrapping
    {
        return DB::transaction(function () use ($scrapping, $data) {
            if ($scrapping->status !== 'draft') {
                throw new InvalidArgumentException('Submitted asset scrapping cannot be updated.');
            }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $scrapping->company_id,
        $data['scrap_date'] ?? $scrapping->scrap_date,
        'update'
    );
            $assetId = $data['asset_id'] ?? $scrapping->asset_id;

            $asset = $this->getValidAsset($scrapping->company_id, (int) $assetId);

            $scrapDate = $data['scrap_date'] ?? $scrapping->scrap_date?->format('Y-m-d');

            $this->validateScrapDate($asset, $scrapDate);

            [$fixedAssetAccountId, $accumulatedAccountId, $lossAccountId] =
                $this->resolveAccounts($asset);

            $assetCost = round((float) $asset->net_purchase_amount, 2);
            $accumulated = round((float) ($asset->opening_accumulated_depreciation ?? 0), 2);
            $bookValueLoss = round(max($assetCost - $accumulated, 0), 2);

            $scrapping->update([
                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,
                'scrap_date' => $scrapDate,

                'asset_cost' => $assetCost,
                'accumulated_depreciation_amount' => $accumulated,
                'book_value_loss' => $bookValueLoss,

                'fixed_asset_account_id' => $fixedAssetAccountId,
                'accumulated_depreciation_account_id' => $accumulatedAccountId,
                'loss_on_disposal_account_id' => $lossAccountId,
            ]);

            return $scrapping->fresh($this->relations);
        });
    }

    public function submit(AssetScrapping $scrapping, ?int $submittedBy = null): AssetScrapping
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $scrapping->company_id,
        $scrapping->scrap_date,
        'create'
    );
        return DB::transaction(function () use ($scrapping, $submittedBy) {
            if ($scrapping->status !== 'draft') {
                throw new InvalidArgumentException('Only draft asset scrapping can be submitted.');
            }

            $scrapping->load($this->relations);

            $asset = $this->getValidAsset($scrapping->company_id, $scrapping->asset_id);

            $this->validateScrapDate($asset, $scrapping->scrap_date?->format('Y-m-d'));

            $journalEntry = $this->createPostedJournalEntry($scrapping, $submittedBy);

            $asset->update([
                'status' => 'scrapped',
                'asset_quantity' => 0,
                'net_purchase_amount' => 0,
                'opening_accumulated_depreciation' => 0,
            ]);

            $scrapping->update([
                'journal_entry_id' => $journalEntry->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => $submittedBy,
            ]);

            return $scrapping->fresh($this->relations);
        });
    }

    public function delete(AssetScrapping $scrapping): void
    {
        if ($scrapping->status !== 'draft') {
            throw new InvalidArgumentException('Submitted asset scrapping cannot be deleted.');
        }

        $scrapping->delete();
    }

    public function availableAssets(int $companyId): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->where('asset_quantity', '>', 0)
            ->with(['assetItem', 'assetCategory', 'location'])
            ->latest('id')
            ->get();
    }

    private function getValidAsset(int $companyId, int $assetId): Asset
    {
        $asset = Asset::query()
            ->where('company_id', $companyId)
            ->where('id', $assetId)
            ->where('status', 'submitted')
            ->where('asset_quantity', '>', 0)
            ->with(['assetCategory', 'assetItem', 'location'])
            ->first();

        if (! $asset) {
            throw new InvalidArgumentException('Invalid submitted asset selected.');
        }

        return $asset;
    }

    private function validateScrapDate(Asset $asset, string $scrapDate): void
    {
        $purchaseDate = $asset->purchase_date?->format('Y-m-d');

        if ($purchaseDate && $scrapDate < $purchaseDate) {
            throw new InvalidArgumentException('Scrap date must be greater than or equal to purchase date.');
        }

        if ($scrapDate > now()->toDateString()) {
            throw new InvalidArgumentException('Scrap date cannot be in the future.');
        }
    }

    private function resolveAccounts(Asset $asset): array
{
    $category = $asset->assetCategory;

    if (! $category?->fixed_asset_account_id) {
        throw new InvalidArgumentException(
            'Fixed asset account is not configured on asset category.'
        );
    }

    if (! $category?->accumulated_depreciation_account_id) {
        throw new InvalidArgumentException(
            'Accumulated depreciation account is not configured on asset category.'
        );
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $asset->company_id)
        ->first();

    if (! $settings || ! $settings->gain_loss_asset_disposal_account_id) {
    throw new InvalidArgumentException(
        'Gain/Loss on Asset Disposal account is not configured in company default accounts.'
    );
}

return [
    (int) $category->fixed_asset_account_id,
    (int) $category->accumulated_depreciation_account_id,
    (int) $settings->gain_loss_asset_disposal_account_id,
];
}
    private function createPostedJournalEntry(AssetScrapping $scrapping, ?int $userId): JournalEntry
    {
        $totalDebit = round(
            (float) $scrapping->accumulated_depreciation_amount
            + (float) $scrapping->book_value_loss,
            2
        );

        $totalCredit = round((float) $scrapping->asset_cost, 2);

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new InvalidArgumentException('Asset scrapping journal entry is not balanced.');
        }

        $entry = JournalEntry::query()->create([
            'company_id' => $scrapping->company_id,
            'entry_number' => $this->generateJournalEntryNumber($scrapping->company_id),
            'entry_date' => $scrapping->scrap_date,
            'description' => 'Asset scrapping - ' . $scrapping->asset?->asset_name_en,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $userId,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);

        if ((float) $scrapping->accumulated_depreciation_amount > 0) {
            $this->createJournalLine(
                $scrapping,
                $entry,
                (int) $scrapping->accumulated_depreciation_account_id,
                (float) $scrapping->accumulated_depreciation_amount,
                0,
                'Remove accumulated depreciation',
                $userId
            );
        }

        if ((float) $scrapping->book_value_loss > 0) {
            $this->createJournalLine(
                $scrapping,
                $entry,
                (int) $scrapping->loss_on_disposal_account_id,
                (float) $scrapping->book_value_loss,
                0,
                'Loss on asset scrapping',
                $userId
            );
        }

        $this->createJournalLine(
            $scrapping,
            $entry,
            (int) $scrapping->fixed_asset_account_id,
            0,
            (float) $scrapping->asset_cost,
            'Remove fixed asset cost',
            $userId
        );

        return $entry->fresh(['lines.account']);
    }

    private function createJournalLine(
        AssetScrapping $scrapping,
        JournalEntry $entry,
        int $accountId,
        float $debit,
        float $credit,
        string $note,
        ?int $userId
    ): JournalEntryLine {
        $line = JournalEntryLine::query()->create([
            'company_id' => $scrapping->company_id,
            'journal_entry_id' => $entry->id,
            'account_id' => $accountId,
            'debit' => $debit,
            'credit' => $credit,
            'note' => $note,
        ]);

        $lastBalance = GeneralLedger::query()
            ->where('company_id', $scrapping->company_id)
            ->where('account_id', $accountId)
            ->latest('id')
            ->value('balance') ?? 0;

        $newBalance = ((float) $lastBalance + $debit) - $credit;

        GeneralLedger::query()->create([
            'company_id' => $scrapping->company_id,
            'journal_entry_id' => $entry->id,
            'journal_entry_line_id' => $line->id,
            'account_id' => $accountId,
            'entry_date' => $entry->entry_date,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $newBalance,
            'description' => $entry->description,
            'created_by' => $userId,
        ]);

        return $line;
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = AssetScrapping::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'SCRAP-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}