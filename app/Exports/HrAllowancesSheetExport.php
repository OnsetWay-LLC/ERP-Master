<?php

namespace App\Exports;

use App\Models\EmployeeAllowance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HrAllowancesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return EmployeeAllowance::query()
            ->with('employee')
            ->latest('id')
            ->get();
    }

    public function title(): string
    {
        return 'allowances';
    }

    public function headings(): array
    {
        return [
            'employee_national_id',
            'employee_series',
            'employee_name',
            'name_ar',
            'name_en',
            'amount',
        ];
    }

    public function map($allowance): array
    {
        return [
            $allowance->employee?->national_id,
            $allowance->employee?->series,
            $allowance->employee?->full_name_en,
            $allowance->name_ar,
            $allowance->name_en,
            $allowance->amount,
        ];
    }
}