<?php

namespace App\Exports;

use App\Services\Accounting\GeneralLedgerService;
use Maatwebsite\Excel\Concerns\FromArray;

class GeneralLedgerExport implements FromArray
{
    public function __construct(
        private readonly int $companyId,
        private readonly array $filters
    ) {
    }

    public function array(): array
    {
        $report = app(GeneralLedgerService::class)->report($this->companyId, $this->filters);

        $rows = [];

        $rows[] = ['General Ledger'];
        $rows[] = [];
        $rows[] = ['Account', $report['account']['account_number'] . ' - ' . $report['account']['name_en']];
        $rows[] = ['Account Level', $report['account']['account_level']];
        $rows[] = ['From Date', $report['filters']['from_date']];
        $rows[] = ['To Date', $report['filters']['to_date']];
        $rows[] = ['Opening Balance', $report['opening_balance']['display']];
        $rows[] = [];

        $rows[] = [
            'Date',
            'Voucher',
            'Type',
            'Account',
            'Created By',
            'Description',
            'Debit',
            'Credit',
            'Balance',
        ];

        foreach ($report['rows'] as $row) {
            $account = $row['account']['account_number'] . ' - ' . $row['account']['name_en'];

            $rows[] = [
                $row['date'],
                $row['voucher_no'],
                $row['voucher_type'],
                $account,
                $row['created_by']['name'] ?? '',
                $row['description'],
                $row['debit'],
                $row['credit'],
                $row['balance'],
            ];
        }

        $rows[] = [];
        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            'Grand Total',
            $report['grand_total']['total_debit'],
            $report['grand_total']['total_credit'],
            $report['closing_balance']['display'],
        ];

        return $rows;
    }
}