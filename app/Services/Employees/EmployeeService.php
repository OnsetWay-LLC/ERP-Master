<?php
namespace App\Services\Employees;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
class EmployeeService
{
    public function getAll(array $filters)
    {
        $query = Employee::with(['company', 'department', 'shifts', 'educations']);

        // search
        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('company_email', 'like', "%{$search}%");
            });
        }

        // filters
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }


        // soft delete
        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

   public function create(array $data)
{
    return DB::transaction(function () use ($data) {

        $company = Company::query()->firstOrFail();

        $lastSeries = Employee::withTrashed()
            ->where('company_id', $company->id)
            ->whereNotNull('series')
            ->orderByDesc('id')
            ->value('series');

        $lastNumber = $lastSeries
            ? (int) str_replace('EMP', '', $lastSeries)
            : 0;

        $next = $lastNumber + 1;

        $series = 'EMP' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            ...$data,
            'company_id' => $company->id,
            'series' => $series,
        ]);

        if (!empty($data['shifts'])) {
            foreach ($data['shifts'] as $i => $shift) {
                $employee->shifts()->create([
                    ...$shift,
                    'is_default' => $i === 0,
                ]);
            }
        }

        if (!empty($data['educations'])) {
            foreach ($data['educations'] as $edu) {
                $employee->educations()->create($edu);
            }
        }

        return $employee->load(['company', 'department', 'shifts', 'educations']);
    });
}
public function update(Employee $employee, array $data): Employee
{
    return DB::transaction(function () use ($employee, $data) {
        $employee->update($data);

        if (isset($data['shifts'])) {
            $employee->shifts()->delete();

            foreach ($data['shifts'] as $i => $shift) {
                $employee->shifts()->create([
                    ...$shift,
                    'is_default' => $i === 0,
                ]);
            }
        }

        if (isset($data['educations'])) {
            $employee->educations()->delete();

            foreach ($data['educations'] as $edu) {
                $employee->educations()->create($edu);
            }
        }

        return $employee->fresh()->load(['company', 'department', 'shifts', 'educations']);
    });
}

    public function getById(int $id): Employee
    {
        return Employee::with(['company', 'department', 'shifts', 'educations'])->findOrFail($id);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}