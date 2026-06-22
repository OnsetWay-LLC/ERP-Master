<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\JournalEntryLine;
use App\Models\TaxDeclarationSetting;
use Carbon\Carbon;

class TaxDeclarationReportService
{
    public function generate(int $year)
    {
        $company = Company::query()->firstOrFail();

        $setting = TaxDeclarationSetting::where(
            'company_id',
            $company->id
        )->firstOrFail();

        [$fromDate, $toDate] = $this->resolveCurrentPeriod(
            $year,
            $setting->report_month_type
        );

        $outputTax = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->where('chart_of_accounts.account_type', 'output_tax')
            ->sum('credit');

        $inputTax = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->where('chart_of_accounts.account_type', 'input_tax')
            ->sum('debit');

        return [

            'period' => [
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
            ],

            'rows' => [

                [
                    'account' => 'Output Tax',
                    'value' => round($outputTax,2),
                ],

                [
                    'account' => 'Input Tax',
                    'value' => round($inputTax,2),
                ],

            ],

            'output_tax' => round($outputTax,2),

            'input_tax' => round($inputTax,2),

            'tax_due' => round($outputTax - $inputTax,2),

        ];
    }

   private function resolveCurrentPeriod(int $year, string $type): array
{
    $currentMonth = now()->month;

    if ($type === 'odd') {
        $startMonth = $currentMonth % 2 === 1
            ? $currentMonth
            : $currentMonth - 1;
    } else {
        $startMonth = $currentMonth % 2 === 0
            ? $currentMonth
            : $currentMonth - 1;

        if ($startMonth < 1) {
            $startMonth = 12;
            $year = $year - 1;
        }
    }

    $start = Carbon::create($year, $startMonth, 1)->startOfDay();
    $end = (clone $start)->addMonth()->endOfMonth()->endOfDay();

    return [$start, $end];
}
}