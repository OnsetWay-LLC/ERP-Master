<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class HrLeaveBalancesSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['employee_national_id']) || empty($row['leave_type'])) {
                continue;
            }

            $employee = Employee::where('national_id', $row['employee_national_id'])->first();

            if (! $employee) {
                continue;
            }

            $totalDays = (float) ($row['total_days'] ?? 0);
            $usedDays = (float) ($row['used_days'] ?? 0);

            EmployeeLeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type' => $row['leave_type'],
                ],
                [
                    'name_ar' => $row['name_ar'] ?? null,
                    'name_en' => $row['name_en'] ?? null,
                    'total_days' => $totalDays,
                    'used_days' => $usedDays,
                    'remaining_days' => max(0, $totalDays - $usedDays),
                ]
            );
        }
    }
}