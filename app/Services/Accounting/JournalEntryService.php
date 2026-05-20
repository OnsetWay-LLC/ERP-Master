<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalEntryService
{
    public function getAll(int $companyId): Collection
    {
        return JournalEntry::query()
            ->where('company_id', $companyId)
            ->with(['creator', 'lines.account'])
            ->latest('id')
            ->get();
    }

    public function show(int $companyId, int $id): JournalEntry
    {
        return JournalEntry::query()
            ->where('company_id', $companyId)
            ->with(['creator', 'lines.account', 'reversalEntries.lines.account'])
            ->findOrFail($id);
    }

    public function create(array $data, int $companyId, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            $this->validateLines($data['lines'], $companyId);

            $totalDebit = collect($data['lines'])->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $totalCredit = collect($data['lines'])->sum(fn ($line) => (float) ($line['credit'] ?? 0));

            $this->ensureBalanced($totalDebit, $totalCredit);

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber($companyId),
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'created_by' => $userId,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'company_id' => $companyId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'note' => $line['note'] ?? null,
                ]);
            }

            return $entry->fresh(['creator', 'lines.account']);
        });
    }

    public function updateDraft(int $companyId, int $id, array $data): JournalEntry
    {
        return DB::transaction(function () use ($companyId, $id, $data) {
            $user = auth()->user();

            $entry = JournalEntry::query()
                ->where('company_id', $companyId)
                ->with('lines')
                ->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new InvalidArgumentException('Only draft journal entries can be updated.');
            }

            if ($user->hasRole('Accountant Sub') && (int) $entry->created_by !== (int) $user->id) {
                throw new InvalidArgumentException('You can only update your own draft journal entries.');
            }

            $this->validateLines($data['lines'], $companyId);

            $totalDebit = collect($data['lines'])->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $totalCredit = collect($data['lines'])->sum(fn ($line) => (float) ($line['credit'] ?? 0));

            $this->ensureBalanced($totalDebit, $totalCredit);

            $entry->update([
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            $entry->lines()->delete();

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'company_id' => $companyId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'note' => $line['note'] ?? null,
                ]);
            }

            return $entry->fresh(['creator', 'lines.account']);
        });
    }

    public function deleteDraft(int $companyId, int $id): void
    {
        DB::transaction(function () use ($companyId, $id) {
            $user = auth()->user();

            $entry = JournalEntry::query()
                ->where('company_id', $companyId)
                ->with('lines')
                ->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new InvalidArgumentException('Only draft journal entries can be deleted.');
            }

            if ($user->hasRole('Accountant Sub') && (int) $entry->created_by !== (int) $user->id) {
                throw new InvalidArgumentException('You can only delete your own draft journal entries.');
            }

            $entry->lines()->delete();
            $entry->delete();
        });
    }

    public function submit(int $companyId, int $id): JournalEntry
    {
        return DB::transaction(function () use ($companyId, $id) {
            $user = auth()->user();

            if (! $user->hasAnyRole(['Accountant Chief', 'CFO'])) {
                throw new InvalidArgumentException('You are not allowed to submit journal entries.');
            }

            $entry = JournalEntry::query()
                ->where('company_id', $companyId)
                ->with('lines.account')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new InvalidArgumentException('Only draft entries can be submitted.');
            }

            $totalDebit = $entry->lines->sum(fn ($line) => (float) $line->debit);
            $totalCredit = $entry->lines->sum(fn ($line) => (float) $line->credit);

            $this->ensureBalanced($totalDebit, $totalCredit);

            foreach ($entry->lines as $line) {
                $lastBalance = GeneralLedger::query()
                    ->where('company_id', $companyId)
                    ->where('account_id', $line->account_id)
                    ->latest('id')
                    ->value('balance') ?? 0;

                $newBalance = ((float) $lastBalance + (float) $line->debit) - (float) $line->credit;

                GeneralLedger::create([
                    'company_id' => $companyId,
                    'journal_entry_id' => $entry->id,
                    'journal_entry_line_id' => $line->id,
                    'account_id' => $line->account_id,
                    'entry_date' => $entry->entry_date,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'balance' => $newBalance,
                    'description' => $entry->description,
                    'created_by' => $entry->created_by,
                ]);
            }

            $entry->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            return $entry->fresh(['creator', 'lines.account']);
        });
    }

    public function cancel(int $companyId, int $id): JournalEntry
    {
        return DB::transaction(function () use ($companyId, $id) {
            $user = auth()->user();

            if (! $user->hasAnyRole(['Accountant Chief', 'CFO'])) {
                throw new InvalidArgumentException('You are not allowed to cancel journal entries.');
            }

            $originalEntry = JournalEntry::query()
                ->where('company_id', $companyId)
                ->with('lines')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($originalEntry->status !== 'posted') {
                throw new InvalidArgumentException('Only posted journal entries can be cancelled.');
            }

            $alreadyReversed = JournalEntry::query()
                ->where('company_id', $companyId)
                ->where('reversed_entry_id', $originalEntry->id)
                ->exists();

            if ($alreadyReversed) {
                throw new InvalidArgumentException('This journal entry is already cancelled.');
            }

            $reversalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber($companyId),
                'entry_date' => now()->toDateString(),
                'description' => 'Reversal of journal entry ' . $originalEntry->entry_number,
                'status' => 'draft',
                'created_by' => $user->id,
                'total_debit' => $originalEntry->total_credit,
                'total_credit' => $originalEntry->total_debit,
                'reversed_entry_id' => $originalEntry->id,
            ]);

            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'company_id' => $companyId,
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'note' => 'Reversal line for journal entry ' . $originalEntry->entry_number,
                ]);
            }

            $this->submit($companyId, $reversalEntry->id);

            $originalEntry->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $originalEntry->fresh([
                'creator',
                'lines.account',
                'reversalEntries.lines.account',
            ]);
        });
    }

    public function getAccountsDropdown(int $companyId)
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('account_level', 'child')
            ->orderBy('account_number')
            ->get([
                'id',
                'account_number',
                'name_ar',
                'name_en',
                'account_level',
                'account_type',
                'root_category',
                'sub_category',
            ]);
    }

    private function validateLines(array $lines, int $companyId): void
    {
        foreach ($lines as $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                throw new InvalidArgumentException('Debit and credit cannot exist together in same row.');
            }

            if ($debit <= 0 && $credit <= 0) {
                throw new InvalidArgumentException('Debit or credit is required.');
            }

            $account = ChartOfAccount::query()
                ->where('company_id', $companyId)
                ->where('id', $line['account_id'])
                ->whereNull('deleted_at')
                ->firstOrFail();

            if ($account->account_level !== 'child') {
                throw new InvalidArgumentException('Only child accounts are allowed in journal entries.');
            }

            if (! $account->is_active) {
                throw new InvalidArgumentException('Inactive accounts are not allowed.');
            }
        }
    }

    private function ensureBalanced(float $totalDebit, float $totalCredit): void
    {
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new InvalidArgumentException('Journal entry is not balanced.');
        }
    }

    private function generateEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}