<?php

namespace App\Services\Warehouse;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    public function getAll(array $filters)
    {
        $query = Warehouse::with([
            'company',
            'creator',
            'parent',
            'salesPerson',
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('is_group', $filters)) {
            $query->where('is_group', filter_var($filters['is_group'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (!empty($filters['sales_person_id'])) {
            $query->where('sales_person_id', $filters['sales_person_id']);
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest('id')->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            $data['company_id'] = $company->id;
            $data['created_by'] = auth('api')->id();

            if (($data['is_group'] ?? false) === true) {
                $data['parent_id'] = null;
                $data['sales_person_id'] = null;
                $data['opening_balance'] = 0;
            } else {
                $data['opening_balance'] = $data['opening_balance'] ?? 0;
            }

            return Warehouse::create($data)->load([
                'company',
                'creator',
                'parent',
                'salesPerson',
            ]);
        });
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $isGroup = array_key_exists('is_group', $data)
                ? (bool) $data['is_group']
                : (bool) $warehouse->is_group;

            if ($isGroup === true) {
                $data['parent_id'] = null;
                $data['sales_person_id'] = null;
                $data['opening_balance'] = 0;
            } else {
                $data['opening_balance'] = $data['opening_balance'] ?? $warehouse->opening_balance ?? 0;
            }

            $warehouse->update($data);

            return $warehouse->fresh()->load([
                'company',
                'creator',
                'parent',
                'salesPerson',
            ]);
        });
    }

    public function delete(Warehouse $warehouse): void
    {
        if ($warehouse->children()->exists()) {
            abort(422, 'Cannot delete parent warehouse because it has child warehouses.');
        }

        $warehouse->delete();
    }
}