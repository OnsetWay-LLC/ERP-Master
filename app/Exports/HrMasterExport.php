<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HrMasterExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'employees' => new HrEmployeesSheetExport(),
            'allowances' => new HrAllowancesSheetExport(),
            'leave_balances' => new HrLeaveBalancesSheetExport(),
        ];
    }
}