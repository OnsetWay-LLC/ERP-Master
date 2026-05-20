<?php

namespace App\Services\AssetItem;

use App\Models\AssetCategory;
use App\Models\AssetItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetItemService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return AssetItem::query()
            ->where('company_id', $companyId)
            ->with('assetCategory')
            ->when($filters['asset_category_id'] ?? null, fn ($q, $v) => $q->where('asset_category_id', $v))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', request()->boolean('is_active')))
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetItem
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $this->validateAssetCategory($companyId, (int) $data['asset_category_id']);

            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;
            $data['is_active'] = $data['is_active'] ?? true;

            return AssetItem::create($data)->load('assetCategory');
        });
    }

    public function update(AssetItem $assetItem, array $data): AssetItem
    {
        return DB::transaction(function () use ($assetItem, $data) {
            if (isset($data['asset_category_id'])) {
                $this->validateAssetCategory(
                    $assetItem->company_id,
                    (int) $data['asset_category_id']
                );
            }

            $assetItem->update($data);

            return $assetItem->fresh('assetCategory');
        });
    }

    public function delete(AssetItem $assetItem): void
    {
        $assetItem->delete();
    }

    private function validateAssetCategory(int $companyId, int $assetCategoryId): void
    {
        $category = AssetCategory::query()
            ->where('company_id', $companyId)
            ->where('id', $assetCategoryId)
            ->where('is_active', true)
            ->first();

        if (! $category) {
            throw new InvalidArgumentException('Invalid asset category selected.');
        }
    }
}