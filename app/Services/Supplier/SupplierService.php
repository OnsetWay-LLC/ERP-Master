<?php

namespace App\Services\Supplier;

use App\Models\Supplier;
use App\Models\Company;

class SupplierService
{
    public function getAll($filters)
    {
        $query = Supplier::with(['company', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('supplier_name_ar', 'like', "%{$search}%")
                  ->orWhere('supplier_name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['supplier_type'])) {
            $query->where('supplier_type', $filters['supplier_type']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
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

        return Supplier::create($data);
    }

    public function update($supplier, $data)
    {
        $supplier->update($data);
        return $supplier;
    }

    public function delete($supplier)
    {
        $supplier->delete();
    }
}