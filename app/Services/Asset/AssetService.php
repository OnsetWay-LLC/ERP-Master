<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\AssetItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->with(['assetItem', 'assetCategory', 'location'])
            ->when($filters['asset_item_id'] ?? null, fn ($q, $v) => $q->where('asset_item_id', $v))
            ->when($filters['asset_category_id'] ?? null, fn ($q, $v) => $q->where('asset_category_id', $v))
            ->when($filters['location_id'] ?? null, fn ($q, $v) => $q->where('location_id', $v))
            ->when($filters['asset_type'] ?? null, fn ($q, $v) => $q->where('asset_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest('id')
            ->get();
    }

  public function create(array $data, int $companyId, ?int $createdBy = null): Asset
{
    return DB::transaction(function () use ($data, $companyId, $createdBy) {
        $assetItem = $this->getValidAssetItem($companyId, (int) $data['asset_item_id']);

        $data['company_id'] = $companyId;
        $data['asset_category_id'] = $assetItem->asset_category_id;
        $data['asset_name_ar'] = $assetItem->item_name;
        $data['asset_name_en'] = $assetItem->item_name;
        $data['series'] = $this->generateSeries($companyId);
        $data['created_by'] = $createdBy;
        $data['status'] = 'draft';

        $data['opening_accumulated_depreciation'] = $data['opening_accumulated_depreciation'] ?? 0;
        $data['opening_number_of_booked_depreciations'] = $data['opening_number_of_booked_depreciations'] ?? 0;

        $this->validateOpeningDepreciationFields($data);

        if ($data['asset_type'] !== 'existing_asset') {
            $data['opening_accumulated_depreciation'] = 0;
            $data['opening_number_of_booked_depreciations'] = 0;
        }

        if ($data['asset_type'] === 'existing_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
        }

        if ($data['asset_type'] === 'composite_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
            $data['net_purchase_amount'] = 0;
        }

        if ($data['asset_type'] === 'composite_component') {
            $invoiceData = $this->resolvePurchaseInvoiceData(
    $companyId,
    (int) $data['purchase_invoice_id'],
    $assetItem->item_code
);

            $data['purchase_date'] = $invoiceData['purchase_date'];
            $data['net_purchase_amount'] = $invoiceData['net_purchase_amount'];
            $data['purchase_receipt_id'] = $invoiceData['purchase_receipt_id'];
        }

        $transactionDate = $data['purchase_date']
            ?? $data['available_for_use_date']
            ?? now()->toDateString();

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                $companyId,
                $transactionDate,
                'create'
            );

        if ($data['available_for_use_date'] < $data['purchase_date']) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        return Asset::create($data)->load([
            'assetItem',
            'assetCategory',
            'location',
        ]);
    });
}

public function update(Asset $asset, array $data): Asset
{
    return DB::transaction(function () use ($asset, $data) {
        if ($asset->status === 'submitted') {
            throw new InvalidArgumentException('Submitted asset cannot be updated.');
        }

        $transactionDate = $data['purchase_date']
            ?? $data['available_for_use_date']
            ?? $asset->purchase_date?->format('Y-m-d')
            ?? $asset->available_for_use_date?->format('Y-m-d');

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                $asset->company_id,
                $transactionDate,
                'update'
            );

        $assetItem = isset($data['asset_item_id'])
            ? $this->getValidAssetItem($asset->company_id, (int) $data['asset_item_id'])
            : $asset->assetItem;

        if (isset($data['asset_item_id'])) {
            $data['asset_category_id'] = $assetItem->asset_category_id;
            $data['asset_name_ar'] = $assetItem->item_name;
            $data['asset_name_en'] = $assetItem->item_name;
        }

        $assetType = $data['asset_type'] ?? $asset->asset_type;

        $data['opening_accumulated_depreciation'] = $data['opening_accumulated_depreciation']
            ?? $asset->opening_accumulated_depreciation
            ?? 0;

        $data['opening_number_of_booked_depreciations'] = $data['opening_number_of_booked_depreciations']
            ?? $asset->opening_number_of_booked_depreciations
            ?? 0;

        $this->validateOpeningDepreciationFields($data);

        if ($assetType !== 'existing_asset') {
            $data['opening_accumulated_depreciation'] = 0;
            $data['opening_number_of_booked_depreciations'] = 0;
        }

        if ($assetType === 'existing_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
        }

        if ($assetType === 'composite_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
            unset($data['net_purchase_amount']);
        }

        if ($assetType === 'composite_component') {
            $purchaseInvoiceId = $data['purchase_invoice_id'] ?? $asset->purchase_invoice_id;

            if (! $purchaseInvoiceId) {
                throw new InvalidArgumentException('Purchase invoice is required for composite component assets.');
            }

          $invoiceData = $this->resolvePurchaseInvoiceData(
    $asset->company_id,
    (int) $purchaseInvoiceId,
    $assetItem->item_code
);

            $data['purchase_invoice_id'] = $purchaseInvoiceId;
            $data['purchase_date'] = $invoiceData['purchase_date'];
            $data['net_purchase_amount'] = $invoiceData['net_purchase_amount'];
            $data['purchase_receipt_id'] = $invoiceData['purchase_receipt_id'];
        }

        $purchaseDate = $data['purchase_date'] ?? $asset->purchase_date?->format('Y-m-d');
        $availableDate = $data['available_for_use_date'] ?? $asset->available_for_use_date?->format('Y-m-d');

        if ($availableDate < $purchaseDate) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        $asset->update($data);

        return $asset->fresh([
            'assetItem',
            'assetCategory',
            'location',
        ]);
    });
}

public function submit(Asset $asset): Asset
{
    return DB::transaction(function () use ($asset) {
        if ($asset->status !== 'draft') {
            throw new InvalidArgumentException('Only draft assets can be submitted.');
        }

        $asset->loadMissing(['assetItem', 'assetCategory', 'location']);

        $transactionDate = $asset->purchase_date?->format('Y-m-d')
            ?? $asset->available_for_use_date?->format('Y-m-d')
            ?? now()->toDateString();

        app(\App\Services\FinancialYear\FinancialYearService::class)
            ->validateTransactionDate(
                $asset->company_id,
                $transactionDate,
                'create'
            );

        if ($asset->available_for_use_date < $asset->purchase_date) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        if ($asset->asset_type === 'composite_asset') {
            $this->validateCompositeAssetCapitalized($asset);
        }

        if ($asset->asset_type === 'composite_component' && ! $asset->purchase_invoice_id) {
            throw new InvalidArgumentException('Purchase invoice is required for composite component assets.');
        }

        if ($asset->asset_type === 'existing_asset') {
            $this->createExistingAssetOpeningJournalEntry($asset);

            if ((float) $asset->opening_accumulated_depreciation > 0) {
                $this->createOpeningAccumulatedDepreciationJournalEntry($asset);
            }
        }

        $this->createDepreciationSchedule($asset);

        $asset->update([
            'status' => 'submitted',
        ]);

       return $asset->fresh([
    'assetItem',
    'assetCategory',
    'location',
    'journalEntries.lines.account',
]);
    });
}

private function resolvePurchaseInvoiceData(
    int $companyId,
    int $purchaseInvoiceId,
    string $itemCode
): array {
    $invoice = DB::table('purchase_invoices')
        ->where('company_id', $companyId)
        ->where('id', $purchaseInvoiceId)
        ->where('status', 'submitted')
        ->first();

    if (! $invoice) {
        throw new InvalidArgumentException('Invalid submitted purchase invoice selected.');
    }

    $itemLine = DB::table('purchase_invoice_items')
        ->where('purchase_invoice_id', $purchaseInvoiceId)
        ->where('item_code', $itemCode)
        ->first();

    if (! $itemLine) {
        throw new InvalidArgumentException('Selected purchase invoice does not contain the selected asset item code.');
    }

    return [
        'purchase_date' => $invoice->posting_date,
        'net_purchase_amount' => (float) $itemLine->amount,
        'purchase_receipt_id' => $invoice->purchase_receipt_id ?? null,
    ];
}
private function generateSeries(int $companyId): string
{
    $lastId = Asset::query()
        ->where('company_id', $companyId)
        ->max('id') ?? 0;

    return 'AST-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
}
  public function delete(Asset $asset): void
{
    if ($asset->status !== 'draft') {
        throw new InvalidArgumentException('Only draft assets can be deleted.');
    }

    $asset->delete();
}
private function validateOpeningDepreciationFields(array $data): void
{
    $openingAccumulated = $data['opening_accumulated_depreciation'] ?? 0;
    $openingBooked = $data['opening_number_of_booked_depreciations'] ?? 0;

    if ((float) $openingAccumulated < 0) {
        throw new InvalidArgumentException('Opening accumulated depreciation cannot be negative.');
    }

    if ((int) $openingBooked < 0) {
        throw new InvalidArgumentException('Opening number of booked depreciations cannot be negative.');
    }

    if (isset($data['opening_number_of_booked_depreciations']) && ! is_numeric($data['opening_number_of_booked_depreciations'])) {
        throw new InvalidArgumentException('Opening number of booked depreciations must be an integer.');
    }

    if ((float) $openingBooked != (int) $openingBooked) {
        throw new InvalidArgumentException('Opening number of booked depreciations must be an integer.');
    }
}

private function validatePostingAccount(
    int $companyId,
    int $accountId,
    string $label
): int {
    $account = DB::table('chart_of_accounts')
        ->where('company_id', $companyId)
        ->where('id', $accountId)
        ->where('account_level', 'child')
        ->where('is_active', true)
        ->whereNull('deleted_at')
        ->first();

    if (! $account) {
        throw new InvalidArgumentException($label . ' must be an active child account.');
    }

    return (int) $account->id;
}
    private function getValidAssetItem(int $companyId, int $assetItemId): AssetItem
    {
        $assetItem = AssetItem::query()
            ->where('company_id', $companyId)
            ->where('id', $assetItemId)
            ->where('is_active', true)
            ->with('assetCategory')
            ->first();

        if (! $assetItem) {
            throw new InvalidArgumentException('Invalid asset item selected.');
        }

        if (! $assetItem->assetCategory || ! $assetItem->assetCategory->is_active) {
            throw new InvalidArgumentException('Asset item category is not active.');
        }

        return $assetItem;
    }
private function validateCompositeAssetCapitalized(Asset $asset): void
{
    if ((float) $asset->net_purchase_amount <= 0) {
        throw new InvalidArgumentException('Composite asset must be capitalized before submit.');
    }

    $hasSubmittedCapitalization = DB::table('asset_capitalizations')
        ->where('company_id', $asset->company_id)
        ->where('target_asset_id', $asset->id)
        ->where('status', 'submitted')
        ->exists();

    if (! $hasSubmittedCapitalization) {
        throw new InvalidArgumentException('Submitted asset capitalization is required before submitting composite asset.');
    }
}
private function createExistingAssetOpeningJournalEntry(Asset $asset): void
{
    $category = $asset->assetCategory;

    if (! $category || ! $category->fixed_asset_account_id) {
        throw new InvalidArgumentException('Fixed asset account is required in asset category.');
    }

    $openingBalanceEquityAccountId = $this->getOpeningBalanceEquityAccountId($asset->company_id);
$fixedAssetAccountId = $this->validatePostingAccount(
    $asset->company_id,
    (int) $category->fixed_asset_account_id,
    'Fixed Asset Account'
);

$openingBalanceEquityAccountId = $this->validatePostingAccount(
    $asset->company_id,
    (int) $openingBalanceEquityAccountId,
    'Opening Balance Equity Account'
);
    $amount = (float) $asset->net_purchase_amount;

    if ($amount <= 0) {
        throw new InvalidArgumentException('Net purchase amount must be greater than zero.');
    }

    $journalEntryId = DB::table('journal_entries')->insertGetId([
        'company_id' => $asset->company_id,
        'entry_number' => $this->generateJournalEntryNumber($asset->company_id),
        'entry_date' => $asset->purchase_date,
        'source_type' => 'asset',
        'asset_id' => $asset->id,
        'status' => 'posted',
        'total_debit' => $amount,
        'total_credit' => $amount,
        'created_by' => auth('api')->id(),
        'posted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('journal_entry_lines')->insert([
        [
             'company_id' => $asset->company_id,
            'journal_entry_id' => $journalEntryId,
           'account_id' => $fixedAssetAccountId,
            'debit' => $amount,
            'credit' => 0,
           'note' => 'Asset opening entry - ' . $asset->series,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
             'company_id' => $asset->company_id,
            'journal_entry_id' => $journalEntryId,
            'account_id' => $openingBalanceEquityAccountId,
            'debit' => 0,
            'credit' => $amount,
            'note' => 'Opening balance equity - ' . $asset->series,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}


private function createOpeningAccumulatedDepreciationJournalEntry(Asset $asset): void
{
    $category = $asset->assetCategory;

    if (! $category || ! $category->accumulated_depreciation_account_id) {
        throw new InvalidArgumentException('Accumulated depreciation account is required in asset category.');
    }

    $openingBalanceEquityAccountId = $this->getOpeningBalanceEquityAccountId($asset->company_id);

    $openingBalanceEquityAccountId = $this->validatePostingAccount(
        $asset->company_id,
        (int) $openingBalanceEquityAccountId,
        'Opening Balance Equity Account'
    );

    $accumulatedDepreciationAccountId = $this->validatePostingAccount(
        $asset->company_id,
        (int) $category->accumulated_depreciation_account_id,
        'Accumulated Depreciation Account'
    );

    $amount = round((float) $asset->opening_accumulated_depreciation, 2);

    if ($amount <= 0) {
        return;
    }

    $journalEntryId = DB::table('journal_entries')->insertGetId([
        'company_id' => $asset->company_id,
        'entry_number' => $this->generateJournalEntryNumber($asset->company_id),
        'entry_date' => $asset->purchase_date,
        'source_type' => 'asset_opening_depreciation',
        'asset_id' => $asset->id,
        'status' => 'posted',
        'total_debit' => $amount,
        'total_credit' => $amount,
        'created_by' => auth('api')->id(),
        'posted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('journal_entry_lines')->insert([
        'company_id' => $asset->company_id,
        'journal_entry_id' => $journalEntryId,
        'account_id' => $openingBalanceEquityAccountId,
        'debit' => $amount,
        'credit' => 0,
        'note' => 'Opening accumulated depreciation - ' . $asset->series,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('journal_entry_lines')->insert([
        'company_id' => $asset->company_id,
        'journal_entry_id' => $journalEntryId,
        'account_id' => $accumulatedDepreciationAccountId,
        'debit' => 0,
        'credit' => $amount,
        'note' => 'Opening accumulated depreciation - ' . $asset->series,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
private function getOpeningBalanceEquityAccountId(int $companyId): int
{
    $account = DB::table('chart_of_accounts')
        ->where('company_id', $companyId)
        ->where('account_level', 'child')
        ->where('is_active', true)
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->where('sub_category', 'opening_balance_equity')
              ->orWhere('account_type', 'opening_balance_equity')
              ->orWhere('name_en', 'like', '%Opening Balance Equity%')
              ->orWhere('name_ar', 'like', '%رصيد افتتاحي%');
        })
        ->first();

    if (! $account) {
        throw new InvalidArgumentException('Opening Balance Equity child account is not defined.');
    }

    return (int) $account->id;
}
private function generateJournalEntryNumber(int $companyId): string
{
    $lastId = DB::table('journal_entries')
        ->where('company_id', $companyId)
        ->max('id') ?? 0;

    return 'JV-' . now()->format('Y') . '-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
}


private function createDepreciationSchedule(Asset $asset): void
{
    $category = $asset->assetCategory;

    if (! $category) {
        throw new InvalidArgumentException('Asset category is required.');
    }

    $method = $category->depreciation_method;

    if ($method === 'manual') {
        return;
    }

    if (! $category->frequency_month || (int) $category->frequency_month <= 0) {
        throw new InvalidArgumentException('Frequency of depreciation is required.');
    }

    if (! $category->total_depreciation_count || (int) $category->total_depreciation_count <= 0) {
        throw new InvalidArgumentException('Total number of depreciation is required.');
    }

    if (! $category->depreciation_posting_day || (int) $category->depreciation_posting_day <= 0) {
        throw new InvalidArgumentException('Depreciation posting day is required.');
    }

    if (! $category->depreciation_expense_account_id || ! $category->accumulated_depreciation_account_id) {
        throw new InvalidArgumentException('Depreciation accounts are required in asset category.');
    }

    $netPurchaseAmount = round((float) $asset->net_purchase_amount, 3);

    if ($netPurchaseAmount <= 0) {
        return;
    }

    $frequencyMonth = (int) $category->frequency_month;
    $totalCount = (int) $category->total_depreciation_count;
    $postingDay = (int) $category->depreciation_posting_day;

    $openingBooked = (int) ($asset->opening_number_of_booked_depreciations ?? 0);
    $openingAccumulated = round((float) ($asset->opening_accumulated_depreciation ?? 0), 3);

    if ($openingBooked >= $totalCount) {
        return;
    }

    $remainingCount = $totalCount - $openingBooked;
    $bookValue = round($netPurchaseAmount - $openingAccumulated, 3);

    if ($bookValue <= 0) {
        return;
    }

    DB::table('asset_depreciation_schedules')
        ->where('asset_id', $asset->id)
        ->where('status', 'pending')
        ->delete();

    $startDate = $asset->available_for_use_date->copy();

    for ($i = 1; $i <= $remainingCount; $i++) {
        $scheduleNo = $openingBooked + $i;

        $depreciationAmount = match ($method) {
            'straight_line' => $this->calculateStraightLineDepreciation(
                $bookValue,
                $remainingCount - $i + 1
            ),

            'written_down_value' => $this->calculateWrittenDownValueDepreciation(
                $bookValue,
                (float) $category->depreciation_rate,
                $frequencyMonth
            ),

            'double_declining_balance' => $this->calculateDoubleDecliningDepreciation(
                $bookValue,
                $totalCount
            ),

            default => throw new InvalidArgumentException('Invalid depreciation method.'),
        };

        $depreciationAmount = round($depreciationAmount, 3);

        if ($depreciationAmount <= 0) {
            break;
        }

        if ($depreciationAmount > $bookValue) {
            $depreciationAmount = $bookValue;
        }

        $scheduleDate = $startDate->copy()
            ->addMonths(($i - 1) * $frequencyMonth);

        $scheduleDate->day(min($postingDay, $scheduleDate->daysInMonth));

        DB::table('asset_depreciation_schedules')->insert([
            'company_id' => $asset->company_id,
            'asset_id' => $asset->id,
            'schedule_no' => $scheduleNo,
            'schedule_date' => $scheduleDate->format('Y-m-d'),
            'depreciation_amount' => $depreciationAmount,
            'book_value_before' => $bookValue,
            'book_value_after' => round($bookValue - $depreciationAmount, 3),
            'status' => 'pending',
            'journal_entry_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bookValue = round($bookValue - $depreciationAmount, 3);

        if ($bookValue <= 0) {
            break;
        }
    }
}
private function calculateStraightLineDepreciation(
    float $bookValue,
    int $remainingCount
): float {
    return $bookValue / $remainingCount;
}
private function calculateWrittenDownValueDepreciation(
    float $bookValue,
    float $annualRate,
    int $frequencyMonth
): float {
    if ($annualRate <= 0) {
        throw new InvalidArgumentException('Depreciation rate is required for written down value method.');
    }

    $periodsPerYear = 12 / $frequencyMonth;

    $periodRate = ($annualRate / 100) / $periodsPerYear;

    return $bookValue * $periodRate;
}
private function calculateDoubleDecliningDepreciation(
    float $bookValue,
    int $totalDepreciationCount
): float {
    $doubleDecliningRate = 2 / $totalDepreciationCount;

    return $bookValue * $doubleDecliningRate;
}
}
  