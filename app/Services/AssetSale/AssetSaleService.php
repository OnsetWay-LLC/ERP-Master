<?php

namespace App\Services\AssetSale;

use App\Models\Asset;
use App\Models\AssetSale;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SalesInvoice;
use App\Models\Warehouse;
use App\Services\SalesInvoice\SalesInvoiceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetSaleService
{
    private array $relations = [
        'asset.assetItem',
        'asset.assetCategory',
        'asset.location',
        'assetCategory',
        'warehouse',
        'journalEntry.lines.account',
        'salesInvoice',
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetSale::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetSale
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['posting_date'],
        'create'
    );
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $asset = $this->getValidAsset($companyId, (int) $data['asset_id']);
            $warehouse = $this->getValidWarehouse($companyId, (int) $data['warehouse_id']);

            $this->validateSellQty($asset, (float) $data['sell_qty']);

            [$fixedAccountId, $accDepAccountId] = $this->resolveAssetAccounts($asset);

            $this->validateAccount($companyId, (int) $data['gain_account_id']);
            $this->validateAccount($companyId, (int) $data['loss_account_id']);

            $paymentAccountIds = $this->resolvePaymentAccounts($companyId, $data);

            $calc = $this->calculateSaleValues(
                asset: $asset,
                sellQty: (float) $data['sell_qty'],
                rate: (float) $data['rate']
            );

            return AssetSale::query()->create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),
                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,

                'sell_qty' => $data['sell_qty'],
                'asset_qty_before_sale' => $asset->asset_quantity,
                'asset_qty_after_sale' => max((float) $asset->asset_quantity - (float) $data['sell_qty'], 0),

                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'] ?? null,
                'warehouse_id' => $warehouse->id,

                'rate' => $data['rate'],
                'sale_value' => $calc['sale_value'],

                'original_asset_cost' => $calc['original_asset_cost'],
                'accumulated_depreciation' => $calc['accumulated_depreciation'],
                'sold_asset_cost' => $calc['sold_asset_cost'],
                'sold_accumulated_depreciation' => $calc['sold_accumulated_depreciation'],
                'book_value' => $calc['book_value'],
                'profit_amount' => $calc['profit_amount'],
                'loss_amount' => $calc['loss_amount'],

                'payment_mode' => $data['payment_mode'],
                'receivable_account_id' => $paymentAccountIds['receivable_account_id'],
                'cash_account_id' => $paymentAccountIds['cash_account_id'],
                'bank_account_id' => $paymentAccountIds['bank_account_id'],

                'fixed_asset_account_id' => $fixedAccountId,
                'accumulated_depreciation_account_id' => $accDepAccountId,
                'gain_account_id' => $data['gain_account_id'],
                'loss_account_id' => $data['loss_account_id'],

                'sales_invoice_id' => $data['sales_invoice_id'] ?? null,

                'status' => 'draft',
                'created_by' => $createdBy,
            ])->fresh($this->relations);
        });
    }

    public function createSalesInvoice(
        array $data,
        int $companyId,
        ?int $createdBy = null
    ): AssetSale {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $asset = $this->getValidAsset($companyId, (int) $data['asset_id']);

            $this->validateSellQty($asset, (float) $data['sell_qty']);

            $invoice = app(SalesInvoiceService::class)->create([
                'customer_id' => $data['customer_id'],
                'sales_person_id' => $data['sales_person_id'],

                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'] ?? now()->format('H:i'),
                'payment_due_date' => $data['payment_due_date'] ?? null,

                'posting_method' => $data['posting_method'] ?? 'manual',
                'payment_mode' => $data['payment_mode'],
                'payment_account_id' => $data['payment_account_id'] ?? null,

                'receivable_account_id' => $data['receivable_account_id'] ?? null,
                'sales_account_id' => $data['sales_account_id'],
                'cogs_account_id' => $data['cogs_account_id'],
                'stock_account_id' => $data['stock_account_id'],

                'discount_percentage' => $data['discount_percentage'] ?? 0,

                'is_asset_sale' => true,

                'items' => [
                    [
                        'item_id' => $asset->assetItem->item_id,
                        'warehouse_id' => $data['warehouse_id'],
                        'quantity' => $data['sell_qty'],
                        'rate' => $data['rate'],
                    ],
                ],

                'tax_template_ids' => $data['tax_template_ids'] ?? [],
                'fees_template_ids' => $data['fees_template_ids'] ?? [],
            ]);

            $sale = $this->create([
                'asset_id' => $asset->id,
                'sell_qty' => $data['sell_qty'],
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'rate' => $data['rate'],
                'payment_mode' => $data['payment_mode'],

                'receivable_account_id' => $data['receivable_account_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,

                'gain_account_id' => $data['gain_account_id'],
                'loss_account_id' => $data['loss_account_id'],

                'sales_invoice_id' => $invoice->id,
            ], $companyId, $createdBy);

            return $sale->fresh($this->relations);
        });
    }

    public function update(AssetSale $sale, array $data): AssetSale
    {
        
        return DB::transaction(function () use ($sale, $data) {
            if ($sale->status !== 'draft') {
                throw new InvalidArgumentException('Submitted asset sale cannot be updated.');
            }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $sale->company_id,
        $data['posting_date'] ?? $sale->posting_date,
        'update'
    );
            $asset = isset($data['asset_id'])
                ? $this->getValidAsset($sale->company_id, (int) $data['asset_id'])
                : $sale->asset()->with(['assetCategory'])->first();

            $sellQty = (float) ($data['sell_qty'] ?? $sale->sell_qty);
            $rate = (float) ($data['rate'] ?? $sale->rate);

            $this->validateSellQty($asset, $sellQty);

            $warehouseId = $data['warehouse_id'] ?? $sale->warehouse_id;
            $warehouse = $this->getValidWarehouse($sale->company_id, (int) $warehouseId);

            [$fixedAccountId, $accDepAccountId] = $this->resolveAssetAccounts($asset);

            $dataForPayment = [
                'payment_mode' => $data['payment_mode'] ?? $sale->payment_mode,
                'receivable_account_id' => $data['receivable_account_id'] ?? $sale->receivable_account_id,
                'cash_account_id' => $data['cash_account_id'] ?? $sale->cash_account_id,
                'bank_account_id' => $data['bank_account_id'] ?? $sale->bank_account_id,
            ];

            $paymentAccountIds = $this->resolvePaymentAccounts($sale->company_id, $dataForPayment);

            $gainAccountId = $data['gain_account_id'] ?? $sale->gain_account_id;
            $lossAccountId = $data['loss_account_id'] ?? $sale->loss_account_id;

            $this->validateAccount($sale->company_id, (int) $gainAccountId);
            $this->validateAccount($sale->company_id, (int) $lossAccountId);

            $calc = $this->calculateSaleValues($asset, $sellQty, $rate);

            $sale->update([
                'asset_id' => $asset->id,
                'asset_category_id' => $asset->asset_category_id,

                'sell_qty' => $sellQty,
                'asset_qty_before_sale' => $asset->asset_quantity,
                'asset_qty_after_sale' => max((float) $asset->asset_quantity - $sellQty, 0),

                'posting_date' => $data['posting_date'] ?? $sale->posting_date?->format('Y-m-d'),
                'posting_time' => $data['posting_time'] ?? $sale->posting_time,
                'warehouse_id' => $warehouse->id,

                'rate' => $rate,
                'sale_value' => $calc['sale_value'],

                'original_asset_cost' => $calc['original_asset_cost'],
                'accumulated_depreciation' => $calc['accumulated_depreciation'],
                'sold_asset_cost' => $calc['sold_asset_cost'],
                'sold_accumulated_depreciation' => $calc['sold_accumulated_depreciation'],
                'book_value' => $calc['book_value'],
                'profit_amount' => $calc['profit_amount'],
                'loss_amount' => $calc['loss_amount'],

                'payment_mode' => $dataForPayment['payment_mode'],
                'receivable_account_id' => $paymentAccountIds['receivable_account_id'],
                'cash_account_id' => $paymentAccountIds['cash_account_id'],
                'bank_account_id' => $paymentAccountIds['bank_account_id'],

                'fixed_asset_account_id' => $fixedAccountId,
                'accumulated_depreciation_account_id' => $accDepAccountId,
                'gain_account_id' => $gainAccountId,
                'loss_account_id' => $lossAccountId,
            ]);

            return $sale->fresh($this->relations);
        });
    }

    public function submit(AssetSale $sale, ?int $submittedBy = null): AssetSale
    {
       app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $sale->company_id,
        $sale->posting_date,
        'create'
    );
        return DB::transaction(function () use ($sale, $submittedBy) {
            if ($sale->status !== 'draft') {
                throw new InvalidArgumentException('Only draft asset sale can be submitted.');
            }

            $sale->load($this->relations);

            if (! $sale->sales_invoice_id) {
                throw new InvalidArgumentException('Sales invoice is required before submitting asset sale.');
            }

            $invoice = SalesInvoice::query()
                ->where('company_id', $sale->company_id)
                ->where('id', $sale->sales_invoice_id)
                ->first();

            if (! $invoice || $invoice->status !== 'submitted') {
                throw new InvalidArgumentException('Sales invoice must be submitted before submitting asset sale.');
            }

            if (! $invoice->is_asset_sale) {
                throw new InvalidArgumentException('Linked sales invoice is not marked as asset sale.');
            }

            $asset = $this->getValidAsset($sale->company_id, $sale->asset_id);
            $this->validateSellQty($asset, (float) $sale->sell_qty);

            $journalEntry = $this->createPostedJournalEntry($sale, $submittedBy);

            $remainingQty = round((float) $asset->asset_quantity - (float) $sale->sell_qty, 2);

            if ($remainingQty <= 0) {
                $asset->update([
                    'asset_quantity' => 0,
                    'net_purchase_amount' => 0,
                    'opening_accumulated_depreciation' => 0,
                    'status' => 'sold',
                ]);
            } else {
                $asset->update([
                    'asset_quantity' => $remainingQty,
                    'net_purchase_amount' => round((float) $asset->net_purchase_amount - (float) $sale->sold_asset_cost, 2),
                    'opening_accumulated_depreciation' => round((float) $asset->opening_accumulated_depreciation - (float) $sale->sold_accumulated_depreciation, 2),
                ]);

                $this->rebuildFutureDepreciationSchedule($asset->fresh(), $sale);
            }

            $sale->update([
                'asset_qty_after_sale' => $remainingQty,
                'journal_entry_id' => $journalEntry->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => $submittedBy,
            ]);

            return $sale->fresh($this->relations);
        });
    }

    public function delete(AssetSale $sale): void
    {
        if ($sale->status !== 'draft') {
            throw new InvalidArgumentException('Submitted asset sale cannot be deleted.');
        }

        $sale->delete();
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

    public function availableWarehouses(int $companyId): Collection
    {
        return Warehouse::query()
            ->where('company_id', $companyId)
            ->latest('id')
            ->get();
    }

    public function availableAccounts(int $companyId): Collection
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
            ->where('asset_quantity', '>', 0)
            ->with(['assetItem', 'assetCategory', 'location'])
            ->first();

        if (! $asset) {
            throw new InvalidArgumentException('Invalid submitted asset selected.');
        }

        return $asset;
    }

    private function getValidWarehouse(int $companyId, int $warehouseId): Warehouse
    {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('id', $warehouseId)
            ->first();

        if (! $warehouse) {
            throw new InvalidArgumentException('Invalid warehouse selected.');
        }

        return $warehouse;
    }

    private function validateSellQty(Asset $asset, float $sellQty): void
    {
        if ($sellQty <= 0) {
            throw new InvalidArgumentException('Sell quantity must be greater than zero.');
        }

        if ($sellQty > (float) $asset->asset_quantity) {
            throw new InvalidArgumentException('Sell quantity must be less than or equal to asset quantity.');
        }
    }

    private function resolveAssetAccounts(Asset $asset): array
    {
        $category = $asset->assetCategory;

        if (! $category?->fixed_asset_account_id) {
            throw new InvalidArgumentException('Fixed asset account is not configured on asset category.');
        }

        if (! $category?->accumulated_depreciation_account_id) {
            throw new InvalidArgumentException('Accumulated depreciation account is not configured on asset category.');
        }

        return [
            (int) $category->fixed_asset_account_id,
            (int) $category->accumulated_depreciation_account_id,
        ];
    }

    private function resolvePaymentAccounts(int $companyId, array $data): array
    {
        $paymentMode = $data['payment_mode'];

        $result = [
            'receivable_account_id' => null,
            'cash_account_id' => null,
            'bank_account_id' => null,
        ];

        if ($paymentMode === 'credit') {
            $this->validateAccount($companyId, (int) $data['receivable_account_id']);
            $result['receivable_account_id'] = (int) $data['receivable_account_id'];
        }

        if ($paymentMode === 'cash') {
            $this->validateAccount($companyId, (int) $data['cash_account_id']);
            $result['cash_account_id'] = (int) $data['cash_account_id'];
        }

        if ($paymentMode === 'bank') {
            $this->validateAccount($companyId, (int) $data['bank_account_id']);
            $result['bank_account_id'] = (int) $data['bank_account_id'];
        }

        return $result;
    }

    private function validateAccount(int $companyId, int $accountId): void
    {
        $exists = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('Invalid account selected.');
        }
    }

    private function calculateSaleValues(Asset $asset, float $sellQty, float $rate): array
    {
        $assetQty = (float) $asset->asset_quantity;

        if ($assetQty <= 0) {
            throw new InvalidArgumentException('Asset quantity must be greater than zero.');
        }

        $ratio = $sellQty / $assetQty;

        $originalAssetCost = (float) $asset->net_purchase_amount;
        $accumulatedDepreciation = (float) ($asset->opening_accumulated_depreciation ?? 0);

        $soldAssetCost = round($originalAssetCost * $ratio, 2);
        $soldAccumulatedDepreciation = round($accumulatedDepreciation * $ratio, 2);
        $bookValue = round($soldAssetCost - $soldAccumulatedDepreciation, 2);

        $saleValue = round($sellQty * $rate, 2);

        $profit = 0;
        $loss = 0;

        if ($saleValue > $bookValue) {
            $profit = round($saleValue - $bookValue, 2);
        }

        if ($saleValue < $bookValue) {
            $loss = round($bookValue - $saleValue, 2);
        }

        return [
            'original_asset_cost' => round($originalAssetCost, 2),
            'accumulated_depreciation' => round($accumulatedDepreciation, 2),
            'sold_asset_cost' => $soldAssetCost,
            'sold_accumulated_depreciation' => $soldAccumulatedDepreciation,
            'book_value' => $bookValue,
            'sale_value' => $saleValue,
            'profit_amount' => $profit,
            'loss_amount' => $loss,
        ];
    }

    private function paymentDebitAccountId(AssetSale $sale): int
    {
        return match ($sale->payment_mode) {
            'cash' => (int) $sale->cash_account_id,
            'bank' => (int) $sale->bank_account_id,
            'credit' => (int) $sale->receivable_account_id,
            default => throw new InvalidArgumentException('Invalid payment mode.'),
        };
    }

    private function createPostedJournalEntry(AssetSale $sale, ?int $userId): JournalEntry
    {
        $entry = JournalEntry::query()->create([
            'company_id' => $sale->company_id,
            'entry_number' => $this->generateJournalEntryNumber($sale->company_id),
            'entry_date' => $sale->posting_date,
            'description' => 'Asset sale - ' . $sale->asset?->asset_name_en,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $userId,
            'total_debit' => round((float) $sale->sale_value + (float) $sale->sold_accumulated_depreciation + (float) $sale->loss_amount, 2),
            'total_credit' => round((float) $sale->sold_asset_cost + (float) $sale->profit_amount, 2),
        ]);

        $this->createJournalLine($sale, $entry, $this->paymentDebitAccountId($sale), (float) $sale->sale_value, 0, 'Asset sale value', $userId);

        if ((float) $sale->sold_accumulated_depreciation > 0) {
            $this->createJournalLine($sale, $entry, (int) $sale->accumulated_depreciation_account_id, (float) $sale->sold_accumulated_depreciation, 0, 'Remove accumulated depreciation', $userId);
        }

        if ((float) $sale->loss_amount > 0) {
            $this->createJournalLine($sale, $entry, (int) $sale->loss_account_id, (float) $sale->loss_amount, 0, 'Loss on asset disposal', $userId);
        }

        $this->createJournalLine($sale, $entry, (int) $sale->fixed_asset_account_id, 0, (float) $sale->sold_asset_cost, 'Remove fixed asset cost', $userId);

        if ((float) $sale->profit_amount > 0) {
            $this->createJournalLine($sale, $entry, (int) $sale->gain_account_id, 0, (float) $sale->profit_amount, 'Gain on asset disposal', $userId);
        }

        return $entry->fresh(['lines.account']);
    }

    private function createJournalLine(
        AssetSale $sale,
        JournalEntry $entry,
        int $accountId,
        float $debit,
        float $credit,
        string $note,
        ?int $userId
    ): JournalEntryLine {
        $line = JournalEntryLine::query()->create([
            'company_id' => $sale->company_id,
            'journal_entry_id' => $entry->id,
            'account_id' => $accountId,
            'debit' => $debit,
            'credit' => $credit,
            'note' => $note,
        ]);

        $lastBalance = GeneralLedger::query()
            ->where('company_id', $sale->company_id)
            ->where('account_id', $accountId)
            ->latest('id')
            ->value('balance') ?? 0;

        $newBalance = ((float) $lastBalance + $debit) - $credit;

        GeneralLedger::query()->create([
            'company_id' => $sale->company_id,
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

    private function rebuildFutureDepreciationSchedule(Asset $asset, AssetSale $sale): void
    {
        // Hook لاحقاً عند وجود جدول depreciation schedules.
        // المطلوب:
        // - حذف/إلغاء أقساط الإهلاك المستقبلية غير المرحلة.
        // - إعادة إنشائها بناءً على الكمية والقيمة المتبقية.
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = AssetSale::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'ASALE-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}