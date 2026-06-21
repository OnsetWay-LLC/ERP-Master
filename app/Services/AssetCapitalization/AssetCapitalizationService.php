<?php

namespace App\Services\AssetCapitalization;

use App\Models\Asset;
use App\Models\AssetCapitalization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetCapitalizationService
{
    private array $relations = [
        'targetAsset.assetCategory',
        'targetAsset.assetItem',
        'targetAsset.location',
        'items.asset.assetItem',
        'items.asset.assetCategory',
        'items.asset.location',
    ];

    public function getAll(int $companyId): Collection
    {
        return AssetCapitalization::query()
            ->where('company_id', $companyId)
            ->with($this->relations)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetCapitalization
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['posting_date'],
        'create'
    );
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $targetAsset = $this->getValidTargetAsset($companyId, (int) $data['target_asset_id']);

            $itemsData = $this->prepareItems(
                companyId: $companyId,
                targetAssetId: $targetAsset->id,
                items: $data['items']
            );

            $capitalization = AssetCapitalization::query()->create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),
                'target_asset_id' => $targetAsset->id,
                'posting_date' => $data['posting_date'],
                'posting_time' => $data['posting_time'],
                'consumed_asset_total_value' => collect($itemsData)->sum('asset_value'),
                'status' => 'draft',
                'created_by' => $createdBy,
            ]);

            foreach ($itemsData as $item) {
                $capitalization->items()->create($item);
            }

            return $capitalization->fresh($this->relations);
        });
    }

    public function update(AssetCapitalization $capitalization, array $data): AssetCapitalization
    {
        
        return DB::transaction(function () use ($capitalization, $data) {
            if ($capitalization->status !== 'draft') {
                throw new InvalidArgumentException('Submitted capitalization cannot be updated.');
            }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $capitalization->company_id,
        $data['posting_date'] ?? $capitalization->posting_date,
        'update'
    );
            $targetAssetId = $data['target_asset_id'] ?? $capitalization->target_asset_id;

            $targetAsset = $this->getValidTargetAsset(
                $capitalization->company_id,
                (int) $targetAssetId
            );

            $updateData = [
                'target_asset_id' => $targetAsset->id,
                'posting_date' => $data['posting_date'] ?? $capitalization->posting_date,
                'posting_time' => $data['posting_time'] ?? $capitalization->posting_time,
            ];

            if (isset($data['items'])) {
                $itemsData = $this->prepareItems(
                    companyId: $capitalization->company_id,
                    targetAssetId: $targetAsset->id,
                    items: $data['items']
                );

                $capitalization->items()->delete();

                foreach ($itemsData as $item) {
                    $capitalization->items()->create($item);
                }

                $updateData['consumed_asset_total_value'] = collect($itemsData)->sum('asset_value');
            }

            $capitalization->update($updateData);

            return $capitalization->fresh($this->relations);
        });
    }

    public function submit(AssetCapitalization $capitalization, ?int $submittedBy = null): AssetCapitalization
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $capitalization->company_id,
        $capitalization->posting_date,
        'create'
    );
        return DB::transaction(function () use ($capitalization, $submittedBy) {
            if ($capitalization->status !== 'draft') {
                throw new InvalidArgumentException('Only draft capitalization can be submitted.');
            }

            $capitalization->load($this->relations);

            $targetAsset = $capitalization->targetAsset;

            if (! $targetAsset) {
                throw new InvalidArgumentException('Target asset is required.');
            }

            if ($targetAsset->asset_type !== 'composite_asset') {
                throw new InvalidArgumentException('Target asset must be composite asset.');
            }

            if ($targetAsset->status !== 'submitted') {
                throw new InvalidArgumentException('Target asset must be submitted.');
            }

            if ($capitalization->items->isEmpty()) {
                throw new InvalidArgumentException('Consumed assets table cannot be empty.');
            }

            $total = 0;

            foreach ($capitalization->items as $item) {
                $asset = $item->asset;

                if (! $asset) {
                    throw new InvalidArgumentException('Invalid consumed asset selected.');
                }

                if ((int) $asset->id === (int) $targetAsset->id) {
                    throw new InvalidArgumentException('Target asset cannot be selected as consumed asset.');
                }

                if ($asset->status !== 'submitted') {
                    throw new InvalidArgumentException('Consumed assets must be submitted only.');
                }

                $currentValue = $this->calculateCurrentAssetValue($asset);

                $item->update([
                    'current_asset_value' => $currentValue,
                    'asset_value' => $currentValue,
                ]);

                $total += $currentValue;
            }

            if ($total <= 0) {
                throw new InvalidArgumentException('Consumed asset total value must be greater than zero.');
            }

            $targetAsset->update([
                'net_purchase_amount' => ((float) $targetAsset->net_purchase_amount + (float) $total),
            ]);

            foreach ($capitalization->items as $item) {
                $item->asset->update([
                    'status' => 'capitalized',
                    'capitalized_to_asset_id' => $targetAsset->id,
                    'capitalized_at' => now(),
                ]);
            }

            $capitalization->update([
                'consumed_asset_total_value' => $total,
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => $submittedBy,
            ]);

            return $capitalization->fresh($this->relations);
        });
    }

    public function delete(AssetCapitalization $capitalization): void
    {
        if ($capitalization->status !== 'draft') {
            throw new InvalidArgumentException('Submitted capitalization cannot be deleted.');
        }

        $capitalization->items()->delete();
        $capitalization->delete();
    }

    public function availableTargetAssets(int $companyId): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->where('asset_type', 'composite_asset')
            ->where('status', 'submitted')
            ->with(['assetItem', 'assetCategory', 'location'])
            ->latest('id')
            ->get();
    }

    public function availableConsumedAssets(int $companyId, ?int $targetAssetId = null): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->when($targetAssetId, fn ($q) => $q->where('id', '!=', $targetAssetId))
            ->with(['assetItem', 'assetCategory', 'location'])
            ->latest('id')
            ->get();
    }

    private function prepareItems(int $companyId, int $targetAssetId, array $items): array
    {
        $result = [];
        $usedAssetIds = [];

        foreach ($items as $row) {
            $assetId = (int) $row['asset_id'];

            if ($assetId === $targetAssetId) {
                throw new InvalidArgumentException('Target asset cannot be selected as consumed asset.');
            }

            if (in_array($assetId, $usedAssetIds, true)) {
                throw new InvalidArgumentException('Same asset cannot be selected more than once.');
            }

            $usedAssetIds[] = $assetId;

            $asset = $this->getValidConsumedAsset($companyId, $assetId);
            $currentValue = $this->calculateCurrentAssetValue($asset);

            $result[] = [
                'asset_id' => $asset->id,
                'asset_name_ar' => $asset->asset_name_ar,
                'asset_name_en' => $asset->asset_name_en,
                'item_code' => $asset->assetItem?->item_code,
                'current_asset_value' => $currentValue,
                'asset_value' => $currentValue,
            ];
        }

        return $result;
    }

    private function getValidTargetAsset(int $companyId, int $assetId): Asset
    {
        $asset = Asset::query()
            ->where('company_id', $companyId)
            ->where('id', $assetId)
            ->where('asset_type', 'composite_asset')
            ->where('status', 'submitted')
            ->with(['assetItem', 'assetCategory', 'location'])
            ->first();

        if (! $asset) {
            throw new InvalidArgumentException('Invalid submitted composite target asset selected.');
        }

        return $asset;
    }

    private function getValidConsumedAsset(int $companyId, int $assetId): Asset
    {
        $asset = Asset::query()
            ->where('company_id', $companyId)
            ->where('id', $assetId)
            ->where('status', 'submitted')
            ->with(['assetItem', 'assetCategory', 'location'])
            ->first();

        if (! $asset) {
            throw new InvalidArgumentException('Invalid consumed asset selected.');
        }

        return $asset;
    }

    private function calculateCurrentAssetValue(Asset $asset): float
    {
        $netPurchaseAmount = (float) $asset->net_purchase_amount;
        $openingAccumulatedDepreciation = (float) ($asset->opening_accumulated_depreciation ?? 0);

        return round(max($netPurchaseAmount - $openingAccumulatedDepreciation, 0), 2);
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = AssetCapitalization::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'ACAP-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}