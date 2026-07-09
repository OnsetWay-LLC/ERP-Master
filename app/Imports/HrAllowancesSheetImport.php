<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class HrAllowancesSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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

            $employee->allowances()->create([
                'name_ar' => $row['name_ar'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'amount' => (float) ($row['amount'] ?? 0),
            ]);
        }
    }
}