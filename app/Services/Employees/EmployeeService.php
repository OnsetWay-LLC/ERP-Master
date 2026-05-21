<?php

namespace App\Services\Employees;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollTaxSetting;
use App\Notifications\EmployeeCreatedNotification;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function getAll(array $filters)
    {
        $query = Employee::query()
            ->with([
                'company',
                'department',
                'shifts',
                'educations',
                'activeSalary',
                'allowances',
                'leaveBalances',
            ]);

        if (! empty($filters['search'])) {
            $search = $filters['search'];

           $query->where(function ($q) use ($search) {
    $q->where('full_name_ar', 'like', "%{$search}%")
        ->orWhere('full_name_en', 'like', "%{$search}%")
        ->orWhere('national_id', 'like', "%{$search}%")
        ->orWhere('job_title', 'like', "%{$search}%");
});
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }


        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest('id')->paginate($filters['per_page'] ?? 10);
    }

   public function create(array $data): Employee
{
    return DB::transaction(function () use ($data) {
        $company = Company::query()->firstOrFail();

        $salaryData = $data['salary'];
        $allowances = $data['allowances'] ?? [];
        $shiftIds = $data['shift_ids'] ?? [];
        $educations = $data['educations'] ?? [];
        $leaveBalances = $data['leave_balances'] ?? [];

        unset(
            $data['salary'],
            $data['allowances'],
            $data['shift_ids'],
            $data['educations'],
            $data['leave_balances']
        );

        $employee = Employee::create([
            ...$data,
            'company_id' => $company->id,
            'series' => $this->generateSeries($company->id),
        ]);

        $salaryData['tax_deduction'] = $this->calculateTaxDeduction(
            companyId: $company->id,
            salaryValue: (float) $salaryData['salary_value'],
            maritalStatus: $employee->marital_status,
            wifeWorkingStatus: $employee->wife_working_status
        );

        $salaryData['social_security_deduction'] = $salaryData['social_security_deduction'] ?? 0;
        $salaryData['insurance_deduction'] = $salaryData['insurance_deduction'] ?? 0;
        $salaryData['effective_from'] = $salaryData['effective_from'] ?? now()->toDateString();
        $salaryData['is_active'] = true;

        if ($salaryData['salary_mode'] !== 'bank_transfer') {
            $salaryData['bank_account_name'] = null;
            $salaryData['bank_account_number'] = null;
            $salaryData['iban'] = null;
        }

        $employee->salaries()->create($salaryData);

        foreach ($allowances as $allowance) {
            $employee->allowances()->create($allowance);
        }

        $this->syncShifts($employee, $shiftIds);

        foreach ($educations as $education) {
            $employee->educations()->create($education);
        }

        foreach ($leaveBalances as $leaveBalance) {
            $employee->leaveBalances()->create([
                'leave_type' => $leaveBalance['leave_type'],
                'name_ar' => $leaveBalance['name_ar'],
                'name_en' => $leaveBalance['name_en'],
                'total_days' => $leaveBalance['total_days'],
                'used_days' => 0,
                'remaining_days' => $leaveBalance['total_days'],
            ]);
        }

        $employee = $employee->fresh()->load([
            'company',
            'department',
            'shifts',
            'educations',
            'activeSalary',
            'allowances',
            'leaveBalances',
        ]);

        

        return $employee;
    });
}
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $salaryData = $data['salary'] ?? null;
            $allowances = $data['allowances'] ?? null;
            $shiftIds = $data['shift_ids'] ?? null;
            $educations = $data['educations'] ?? null;
            $leaveBalances = $data['leave_balances'] ?? null;

            unset(
                $data['salary'],
                $data['allowances'],
                $data['shift_ids'],
                $data['educations'],
                $data['leave_balances']
            );

            $employee->update($data);

            if ($salaryData !== null) {
                $this->replaceActiveSalary($employee->fresh(), $salaryData);
            }

            if ($allowances !== null) {
                $employee->allowances()->delete();

                foreach ($allowances as $allowance) {
                    $employee->allowances()->create($allowance);
                }
            }

            if ($shiftIds !== null) {
                $this->syncShifts($employee, $shiftIds);
            }

            if ($educations !== null) {
                $employee->educations()->delete();

                foreach ($educations as $education) {
                    $employee->educations()->create($education);
                }
            }

            if ($leaveBalances !== null) {
                $employee->leaveBalances()->delete();

                foreach ($leaveBalances as $leaveBalance) {
                    $employee->leaveBalances()->create([
                        'leave_type' => $leaveBalance['leave_type'],
                        'name_ar' => $leaveBalance['name_ar'],
                        'name_en' => $leaveBalance['name_en'],
                        'total_days' => $leaveBalance['total_days'],
                        'used_days' => 0,
                        'remaining_days' => $leaveBalance['total_days'],
                    ]);
                }
            }

            return $employee->fresh()->load([
                'company',
                'department',
                'shifts',
                'educations',
                'activeSalary',
                'allowances',
                'leaveBalances',
            ]);
        });
    }

    public function getById(int $id): Employee
    {
        return Employee::with([
            'company',
            'department',
            'shifts',
            'educations',
            'activeSalary',
            'allowances',
            'leaveBalances',
        ])->findOrFail($id);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    private function generateSeries(int $companyId): string
    {
        $lastSeries = Employee::withTrashed()
            ->where('company_id', $companyId)
            ->whereNotNull('series')
            ->orderByRaw("CAST(REPLACE(series, 'EMP', '') AS BIGINT) DESC")
            ->value('series');

        $lastNumber = $lastSeries
            ? (int) str_replace('EMP', '', $lastSeries)
            : 0;

        return 'EMP' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    private function syncShifts(Employee $employee, array $shiftIds): void
    {
        if (empty($shiftIds)) {
            return;
        }

        $syncData = [];

        foreach ($shiftIds as $index => $shiftId) {
            $syncData[$shiftId] = [
                'is_default' => $index === 0,
            ];
        }

        $employee->shifts()->sync($syncData);
    }

    private function replaceActiveSalary(Employee $employee, array $salaryData): void
    {
        $employee->salaries()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'effective_to' => now()->toDateString(),
            ]);

        $salaryData['tax_deduction'] = $this->calculateTaxDeduction(
            companyId: $employee->company_id,
            salaryValue: (float) $salaryData['salary_value'],
            maritalStatus: $employee->marital_status,
            wifeWorkingStatus: $employee->wife_working_status
        );

        $salaryData['social_security_deduction'] = $salaryData['social_security_deduction'] ?? 0;
        $salaryData['insurance_deduction'] = $salaryData['insurance_deduction'] ?? 0;
        $salaryData['effective_from'] = $salaryData['effective_from'] ?? now()->toDateString();
        $salaryData['is_active'] = true;

        if ($salaryData['salary_mode'] !== 'bank_transfer') {
            $salaryData['bank_account_name'] = null;
            $salaryData['bank_account_number'] = null;
            $salaryData['iban'] = null;
        }

        $employee->salaries()->create($salaryData);
    }

    private function calculateTaxDeduction(
        int $companyId,
        float $salaryValue,
        ?string $maritalStatus,
        ?string $wifeWorkingStatus
    ): float {
        $setting = PayrollTaxSetting::query()
            ->with('brackets')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $setting) {
            abort(422, 'Payroll tax settings are not configured.');
        }

        if ($setting->brackets->isEmpty()) {
            abort(422, 'Payroll tax brackets are not configured.');
        }

        $annualSalary = $salaryValue * 12;

        $exemption = $setting->single_or_working_wife_exemption;

        if ($maritalStatus === 'married' && $wifeWorkingStatus === 'not_working') {
            $exemption = $setting->married_not_working_wife_exemption;
        }

        $taxableAmount = max(0, $annualSalary - (float) $exemption);

        if ($taxableAmount <= 0) {
            return 0;
        }

        $annualTax = 0;

        foreach ($setting->brackets as $bracket) {
            $from = (float) $bracket->from_amount;
            $to = $bracket->to_amount !== null ? (float) $bracket->to_amount : null;
            $rate = (float) $bracket->rate / 100;

            if ($taxableAmount <= $from) {
                continue;
            }

            $upperLimit = $to ?? $taxableAmount;
            $amountInBracket = min($taxableAmount, $upperLimit) - $from;

            if ($amountInBracket > 0) {
                $annualTax += $amountInBracket * $rate;
            }
        }

        return round($annualTax / 12, 2);
    }
     public function restore(Employee $employee): void
    {
        if (!$employee->trashed()) {
            abort(400, 'Employee is not deleted.');
        }

        $employee->restore();   
}
}