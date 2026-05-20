<?php

namespace App\Services\BankReconciliation;

use App\Models\BankAccount;
use App\Models\JournalEntryLine;
use InvalidArgumentException;

class BankReconciliationService
{
    public function calculate(array $data, int $companyId): array
    {
        $bankAccount = BankAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $data['bank_account_id'])
            ->with('bank')
            ->firstOrFail();

        if (! $bankAccount->account_id) {
            throw new InvalidArgumentException('Selected bank account is not linked to chart of accounts.');
        }

        $accountId = $bankAccount->account_id;
        $fromDate = $data['from_reference_date'];
        $toDate = $data['to_reference_date'];
        $bankStatementBalance = (float) $data['closing_balance'];

        $openingBalance = $this->openingBalance($companyId, $accountId, $fromDate);

        $incomingPayments = $this->incomingPayments($companyId, $accountId, $fromDate, $toDate);

        $outgoingPayments = $this->outgoingPayments($companyId, $accountId, $fromDate, $toDate);

        $erpBalance = $openingBalance + $incomingPayments - $outgoingPayments;

        $difference = $bankStatementBalance - $erpBalance;

        return [
            'bank_account' => [
                'id' => $bankAccount->id,
                'account_name_ar' => $bankAccount->account_name_ar,
                'account_name_en' => $bankAccount->account_name_en,
                'bank_id' => $bankAccount->bank_id,
                'bank_name_ar' => $bankAccount->bank?->name_ar,
                'bank_name_en' => $bankAccount->bank?->name_en,
                'chart_account_id' => $bankAccount->account_id,
            ],

            'from_reference_date' => $fromDate,
            'to_reference_date' => $toDate,

            'opening_balance' => round($openingBalance, 2),
            'incoming_payments' => round($incomingPayments, 2),
            'outgoing_payments' => round($outgoingPayments, 2),

            'closing_balance_as_per_erp' => round($erpBalance, 2),
            'closing_balance_as_per_bank_statement' => round($bankStatementBalance, 2),

            'difference' => round($difference, 2),

            'status' => round($difference, 2) == 0.00
                ? 'reconciled'
                : 'unreconciled',

            'message' => round($difference, 2) == 0.00
                ? 'Bank account is reconciled.'
                : 'There is a difference in bank reconciliation and it must be reviewed.',

            'transactions' => $this->transactions($companyId, $accountId, $fromDate, $toDate),
        ];
    }

    private function openingBalance(int $companyId, int $accountId, string $fromDate): float
    {
        return (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.company_id', $companyId)
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<', $fromDate)
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit - journal_entry_lines.credit), 0) as balance')
            ->value('balance');
    }

    private function incomingPayments(int $companyId, int $accountId, string $fromDate, string $toDate): float
    {
        return (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.company_id', $companyId)
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->sum('journal_entry_lines.debit');
    }

    private function outgoingPayments(int $companyId, int $accountId, string $fromDate, string $toDate): float
    {
        return (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.company_id', $companyId)
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->sum('journal_entry_lines.credit');
    }

    private function transactions(int $companyId, int $accountId, string $fromDate, string $toDate): array
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.company_id', $companyId)
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->orderBy('journal_entries.entry_date')
            ->select([
                'journal_entries.id as journal_entry_id',
                'journal_entries.entry_number',
                'journal_entries.entry_date',
                'journal_entries.description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entry_lines.note',
            ])
            ->get()
            ->map(function ($line) {
                return [
                    'journal_entry_id' => $line->journal_entry_id,
                    'entry_number' => $line->entry_number,
                    'entry_date' => $line->entry_date,
                    'description' => $line->description,
                    'type' => $line->debit > 0 ? 'receipt' : 'payment',
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'amount' => $line->debit > 0
                        ? (float) $line->debit
                        : (float) $line->credit,
                    'note' => $line->note,
                ];
            })
            ->toArray();
    }
}