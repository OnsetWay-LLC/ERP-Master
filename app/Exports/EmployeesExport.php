<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Employee::query()
            ->with('activeSalary')
            ->latest('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'full_name_ar',
            'full_name_en',
            'national_id',
            'gender',
            'date_of_joining',
            'status',
            'department_id',
            'mobile_number',
            'company_email',
            'address',
            'marital_status',
            'job_title',
            'wife_working_status',
            'salary_value',
            'salary_mode',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->full_name_ar,
            $employee->full_name_en,
            $employee->national_id,
            $employee->gender,
            $employee->date_of_joining,
            $employee->status,
            $employee->department_id,
            $employee->mobile_number,
            $employee->company_email,
            $employee->address,
            $employee->marital_status,
            $employee->job_title,
            $employee->wife_working_status,
            $employee->activeSalary?->salary_value,
            $employee->activeSalary?->salary_mode,
        ];
    }
}