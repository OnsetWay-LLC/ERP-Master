<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HrMasterImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'employees' => new HrEmployeesSheetImport(),
            'allowances' => new HrAllowancesSheetImport(),
            'leave_balances' => new HrLeaveBalancesSheetImport(),
        ];
    }
}