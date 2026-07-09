<?php

namespace App\Imports;

use App\Services\Employees\EmployeeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class HrEmployeesSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private EmployeeService $employeeService;

    public function __construct()
    {
        $this->employeeService = app(EmployeeService::class);
    }
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['national_id'])) {
                continue;
            }

            $this->employeeService->create([
                'full_name_ar' => $row['full_name_ar'] ?? null,
                'full_name_en' => $row['full_name_en'] ?? null,
                'national_id' => $row['national_id'],
                'gender' => $row['gender'] ?? 'male',
                'date_of_joining' => $row['date_of_joining'] ?? now()->toDateString(),
                'status' => $row['status'] ?? 'active',
                'department_id' => $row['department_id'] ?? null,
                'mobile_number' => $row['mobile_number'] ?? null,
                'company_email' => $row['company_email'] ?? null,
                'address' => $row['address'] ?? null,
                'marital_status' => $row['marital_status'] ?? 'single',
                'job_title' => $row['job_title'] ?? null,

                'wife_working_status' =>
                    ($row['marital_status'] ?? 'single') === 'married'
                        ? (!empty($row['wife_working_status']) ? $row['wife_working_status'] : 'not_working')
                        : null,

                'salary' => [
                    'salary_value' => (float) ($row['salary_value'] ?? 0),
                    'salary_mode' => $row['salary_mode'] ?? 'cash',
                    'effective_from' => $row['salary_effective_from'] ?? now()->toDateString(),
                    'social_security_deduction' => 0,
                    'insurance_deduction' => 0,
                ],

                'shift_ids' => [],
                'allowances' => [],
                'educations' => [],
                'leave_balances' => [],
            ]);
        }
    }
}