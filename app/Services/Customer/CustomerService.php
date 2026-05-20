<?php

namespace App\Services\Customer;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Customer::query()
            ->with(['company', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state_province', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['customer_type'])) {
            $query->where('customer_type', $filters['customer_type']);
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

        return $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10)
            ->appends($filters);
    }

    public function getById(int $id): Customer
    {
        return Customer::query()
            ->with(['company', 'creator'])
            ->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        $company = Company::query()->firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();

        return Customer::create($data)->load(['company', 'creator']);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh()->load(['company', 'creator']);
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
    public function restore(Customer $customer): void
    {
        if (!$customer->trashed()) {
            abort(400, 'Customer is not deleted.');
        }

        $customer->restore();   
}
}