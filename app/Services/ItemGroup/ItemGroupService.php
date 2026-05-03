<?php

namespace App\Services\ItemGroup;

use App\Models\Company;
use App\Models\ItemGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemGroupService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = ItemGroup::query()
            ->with(['company', 'warehouse', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {
                        $warehouseQuery->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10)
            ->appends($filters);
    }

    public function getById(int $id): ItemGroup
    {
        return ItemGroup::query()
            ->with(['company', 'warehouse', 'creator'])
            ->findOrFail($id);
    }

    public function create(array $data): ItemGroup
    {
        $company = Company::query()->firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();

        return ItemGroup::create($data)->load(['company', 'warehouse', 'creator']);
    }

    public function update(ItemGroup $itemGroup, array $data): ItemGroup
    {
        $itemGroup->update($data);

        return $itemGroup->fresh()->load(['company', 'warehouse', 'creator']);
    }

    public function delete(ItemGroup $itemGroup): void
    {
        $itemGroup->delete();
    }
}