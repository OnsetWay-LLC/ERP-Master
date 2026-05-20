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

            if ($data['asset_type'] !== 'composite_component') {
                $data['purchase_receipt_id'] = null;
            }

            $data['company_id'] = $companyId;
            $data['asset_category_id'] = $assetItem->asset_category_id;
            $data['series'] = $this->generateSeries($companyId);
            $data['created_by'] = $createdBy;
            $data['status'] = 'active';
            $data['salvage_value'] = $data['salvage_value'] ?? 0;

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
            if (isset($data['asset_item_id'])) {
                $assetItem = $this->getValidAssetItem(
                    $asset->company_id,
                    (int) $data['asset_item_id']
                );

                $data['asset_category_id'] = $assetItem->asset_category_id;
            }

            $purchaseDate = $data['purchase_date'] ?? $asset->purchase_date?->format('Y-m-d');
            $availableDate = $data['available_for_use_date'] ?? $asset->available_for_use_date?->format('Y-m-d');

            if ($availableDate < $purchaseDate) {
                throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
            }

            $assetType = $data['asset_type'] ?? $asset->asset_type;

            if ($assetType !== 'composite_component') {
                $data['purchase_receipt_id'] = null;
            }

            if ($assetType === 'composite_component' && empty($data['purchase_receipt_id']) && empty($asset->purchase_receipt_id)) {
                throw new InvalidArgumentException('Purchase receipt is required for composite component assets.');
            }

            $asset->update($data);

            return $asset->fresh([
                'assetItem',
                'assetCategory',
                'location',
            ]);
        });
    }

    public function delete(Asset $asset): void
    {
        if ($asset->status === 'disposed') {
            throw new InvalidArgumentException('Disposed asset cannot be deleted.');
        }

        $asset->delete();
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

    private function generateSeries(int $companyId): string
    {
        $lastId = Asset::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'AST-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}