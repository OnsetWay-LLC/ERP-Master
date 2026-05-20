<?php

namespace App\Services\AssetLocation;

use App\Models\AssetLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AssetLocationService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return AssetLocation::query()
            ->where('company_id', $companyId)
            ->when(isset($filters['is_active']), function ($q) use ($filters) {
                return $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetLocation
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;
            $data['is_active'] = $data['is_active'] ?? true;

            return AssetLocation::create($data);
        });
    }

    public function update(AssetLocation $location, array $data): AssetLocation
    {
        return DB::transaction(function () use ($location, $data) {
            $location->update($data);

            return $location->fresh();
        });
    }

    public function delete(AssetLocation $location): void
    {
        $location->delete();
    }
}