<?php

namespace App\Services\User;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function getAll(array $filters = [])
    {
        $query = User::with(['employee.department', 'roles']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                      $employeeQuery->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest('id')->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::with('department')
                ->where('national_id', $data['national_id'])
                ->firstOrFail();

            if ($employee->user()->exists()) {
                throw ValidationException::withMessages([
                    'national_id' => ['This employee already has a user account.'],
                ]);
            }

            $user = User::create([
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'username' => $data['username'],
                'email' => $employee->company_email,
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
                'failed_attempts' => 0,
                'locked_until' => null,
                'is_initial_admin' => false,
            ]);

            $user->assignRole($data['role']);

            return $user->load(['employee.department', 'roles']);
        });
    }

    public function getById(User $user): User
    {
        return $user->load(['employee.department', 'roles']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function findEmployeeByNationalId(string $nationalId): Employee
    {
        return Employee::with('department')
            ->where('national_id', $nationalId)
            ->firstOrFail();
    }
}