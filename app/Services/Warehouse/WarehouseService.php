<?php

namespace App\Services\Warehouse;

use App\Models\Warehouse;
use App\Models\Company;

class WarehouseService
{
    public function getAll($filters)
    {
        $query = Warehouse::with(['company', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function create($data)
    {
        $company = Company::firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();

        return Warehouse::create($data);
    }

    public function update($warehouse, $data)
    {
        $warehouse->update($data);
        return $warehouse;
    }

    public function delete($warehouse)
    {
        $warehouse->delete();
    }
}