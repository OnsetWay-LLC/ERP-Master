<?php

namespace App\Services\Department;

use App\Models\Department;
use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Department::query()->with('company');

        // search
       if (!empty($filters['search'])) {
    $search = $filters['search'];

    $query->where(function ($q) use ($search) {

        // البحث داخل اسم القسم
        $q->where('name_ar', 'like', "%{$search}%")
          ->orWhere('name_en', 'like', "%{$search}%")

        // البحث داخل اسم الشركة
          ->orWhereHas('company', function ($companyQuery) use ($search) {
              $companyQuery->where('name_ar', 'like', "%{$search}%")
                           ->orWhere('name_en', 'like', "%{$search}%");
          });

    });


        }

        // filter by company
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        // trashed filter
        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        $perPage = $filters['per_page'] ?? 10;

        return $query
            ->latest('id')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function getById(int $id): Department
    {
        return Department::with('company')->findOrFail($id);
    }

   
public function create(array $data): Department
{
    $company = Company::query()->firstOrFail();

    $data['company_id'] = $company->id;

    return Department::create($data);
}
    public function update(Department $department, array $data): Department
    {
        $department->update($data);

        return $department->fresh()->load('company');
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}