<?php

namespace App\Exports;

use App\Models\EmployeeAllowance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeAllowancesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return EmployeeAllowance::query()
            ->with('employee')
            ->latest('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'employee_series',
            'employee_national_id',
            'employee_name',
            'name_ar',
            'name_en',
            'amount',
        ];
    }

    public function map($allowance): array
    {
        return [
            $allowance->employee?->series,
            $allowance->employee?->national_id,
            $allowance->employee?->full_name_en,
            $allowance->name_ar,
            $allowance->name_en,
            $allowance->amount,
        ];
    }
}