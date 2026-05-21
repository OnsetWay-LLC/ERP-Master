<?php

namespace App\Services\EmployeeLeave;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Notifications\EmployeeLeaveStatusNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveService
{
    public function searchEmployees(?string $search)
{
    return Employee::query()
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('series', 'like', "%{$search}%");
            });
        })
        ->select([
            'id',
            'series',
            'full_name_ar',
            'full_name_en',
            'company_email',
            'gender',
            'marital_status',
        ])
        ->limit(10)
        ->get();
}
    public function leaveOptions(Employee $employee): array
    {
        $employee->load(['leaveBalances']);

        return [
            'employee' => [
                'id' => $employee->id,
                'series' => $employee->series,
                'full_name_ar' => $employee->full_name_ar,
                'full_name_en' => $employee->full_name_en,
                'company_email' => $employee->company_email,
            ],

            'leave_types' => $employee->leaveBalances->map(fn ($balance) => [
                'leave_type' => $balance->leave_type,
                'name_ar' => $balance->name_ar,
                'name_en' => $balance->name_en,
                'total_days' => $balance->total_days,
                'used_days' => $balance->used_days,
                'remaining_days' => $balance->remaining_days,
                'is_finished' => (float) $balance->remaining_days <= 0,
            ])->values(),
        ];
    }

    public function create(array $data): EmployeeLeave
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::query()
                ->with(['company.workingDays', 'activeSalary', 'leaveBalances'])
                ->findOrFail($data['employee_id']);

            $daysCount = $this->calculateLeaveWorkingDays(
                employee: $employee,
                fromDate: $data['from_date'],
                toDate: $data['to_date']
            );

            if ($daysCount <= 0) {
                abort(422, 'Selected dates do not contain working days.');
            }

            $salaryDeductionAmount = 0;
            $deductFromSalary = (bool) ($data['deduct_from_salary'] ?? false);

            if ($data['status'] === 'approved') {
                $balance = $employee->leaveBalances()
                    ->where('leave_type', $data['leave_type'])
                    ->first();

                if (! $balance) {
                    abort(422, 'This leave type is not available for this employee.');
                }

                if ((float) $balance->remaining_days >= $daysCount) {
                    $balance->update([
                        'used_days' => (float) $balance->used_days + $daysCount,
                        'remaining_days' => (float) $balance->remaining_days - $daysCount,
                    ]);
                } else {
                    if (! $deductFromSalary) {
                        abort(422, 'This employee has no enough leave balance. Please choose deduct from salary or reject the leave.');
                    }

                    $salaryDeductionAmount = $this->calculateSalaryDeduction(
                        employee: $employee,
                        leaveDays: $daysCount,
                        fromDate: $data['from_date']
                    );
                }
            }

            if ($data['status'] === 'rejected') {
                $deductFromSalary = false;
                $salaryDeductionAmount = 0;
            }

            $leave = EmployeeLeave::create([
                'employee_id' => $employee->id,
                'leave_type' => $data['leave_type'],
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'days_count' => $daysCount,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'deduct_from_salary' => $deductFromSalary,
                'salary_deduction_amount' => $salaryDeductionAmount,
                'approved_by' => auth('api')->id(),
                'approved_at' => now(),
            ]);

            $leave->load(['employee']);

            if (! empty($employee->company_email)) {
                $employee->notify(new EmployeeLeaveStatusNotification($leave));
            }

            return $leave->fresh()->load(['employee', 'approver']);
        });
    }

    private function calculateLeaveWorkingDays(Employee $employee, string $fromDate, string $toDate): float
    {
        $workingDays = $employee->company->workingDays
            ->where('is_working_day', true)
            ->pluck('day')
            ->map(fn ($day) => strtolower($day))
            ->toArray();

        if (empty($workingDays)) {
            abort(422, 'Company working days are not configured.');
        }

        $count = 0;

        foreach (CarbonPeriod::create($fromDate, $toDate) as $date) {
            $dayName = strtolower($date->format('l'));

            if (in_array($dayName, $workingDays, true)) {
                $count++;
            }
        }

        return $count;
    }

    private function calculateSalaryDeduction(Employee $employee, float $leaveDays, string $fromDate): float
    {
        $salary = $employee->activeSalary;

        if (! $salary) {
            abort(422, 'Employee active salary is not configured.');
        }

        $monthlyWorkingDays = $this->calculateMonthlyWorkingDays($employee, $fromDate);

        if ($monthlyWorkingDays <= 0) {
            abort(422, 'Monthly working days could not be calculated.');
        }

        $dailySalary = (float) $salary->salary_value / $monthlyWorkingDays;

        return round($dailySalary * $leaveDays, 2);
    }

    private function calculateMonthlyWorkingDays(Employee $employee, string $date): int
    {
        $carbonDate = Carbon::parse($date);

        $start = $carbonDate->copy()->startOfMonth();
        $end = $carbonDate->copy()->endOfMonth();

        $workingDays = $employee->company->workingDays
            ->where('is_working_day', true)
            ->pluck('day')
            ->map(fn ($day) => strtolower($day))
            ->toArray();

        $count = 0;

        foreach (CarbonPeriod::create($start, $end) as $day) {
            if (in_array(strtolower($day->format('l')), $workingDays, true)) {
                $count++;
            }
        }

        return $count;
    }
}