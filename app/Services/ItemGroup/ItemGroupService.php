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
            ->with(['company',  'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
            ; 
            });
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
            ->with(['company',  'creator'])
            ->findOrFail($id);
    }

    public function create(array $data): ItemGroup
    {
        $company = Company::query()->firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();

        return ItemGroup::create($data)->load(['company',  'creator']);
    }

    public function update(ItemGroup $itemGroup, array $data): ItemGroup
    {
        $itemGroup->update($data);

        return $itemGroup->fresh()->load(['company',  'creator']);
    }

    public function delete(ItemGroup $itemGroup): void
    {
        $itemGroup->delete();
    }
  public function restore(ItemGroup $itemGroup): void
{
    if (!$itemGroup->trashed()) {
        abort(400, 'Item group is not deleted.');
    }

    $itemGroup->restore();
}
}


