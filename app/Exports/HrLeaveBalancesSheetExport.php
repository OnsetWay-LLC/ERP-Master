<?php

namespace App\Exports;

use App\Models\EmployeeLeaveBalance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HrLeaveBalancesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return EmployeeLeaveBalance::query()
            ->with('employee')
            ->latest('id')
            ->get();
    }

    public function title(): string
    {
        return 'leave_balances';
    }

    public function headings(): array
    {
        return [
            'employee_national_id',
            'employee_series',
            'employee_name',
            'leave_type',
            'name_ar',
            'name_en',
            'total_days',
            'used_days',
            'remaining_days',
        ];
    }

    public function map($balance): array
    {
        return [
            $balance->employee?->national_id,
            $balance->employee?->series,
            $balance->employee?->full_name_en,
            $balance->leave_type,
            $balance->name_ar,
            $balance->name_en,
            $balance->total_days,
            $balance->used_days,
            $balance->remaining_days,
        ];
    }
}