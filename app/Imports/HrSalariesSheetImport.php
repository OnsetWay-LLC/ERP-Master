<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class HrSalariesSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['employee_national_id'])) {
                continue;
            }

            $employee = Employee::where('national_id', $row['employee_national_id'])->first();

            if (! $employee) {
                continue;
            }

            $employee->salaries()->where('is_active', true)->update([
                'is_active' => false,
                'effective_to' => now()->toDateString(),
            ]);

            $employee->salaries()->create([
                'salary_value' => (float) ($row['salary_value'] ?? 0),
                'salary_mode' => $row['salary_mode'] ?? 'cash',
                'effective_from' => $row['effective_from'] ?? now()->toDateString(),
                'is_active' => true,
                'social_security_deduction' => (float) ($row['social_security_deduction'] ?? 0),
                'insurance_deduction' => (float) ($row['insurance_deduction'] ?? 0),
                'bank_account_name' => $row['bank_account_name'] ?? null,
                'bank_account_number' => $row['bank_account_number'] ?? null,
                'iban' => $row['iban'] ?? null,
            ]);
        }
    }
}