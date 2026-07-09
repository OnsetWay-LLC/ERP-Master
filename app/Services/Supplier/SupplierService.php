<?php

namespace App\Services\Supplier;

use App\Models\Supplier;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\CompanyAccountSetting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplierService
{
    public function getAll($filters)
    {
        $query = Supplier::with(['company', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('supplier_name_ar', 'like', "%{$search}%")
                  ->orWhere('supplier_name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['supplier_type'])) {
            $query->where('supplier_type', $filters['supplier_type']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

   public function create($data)
{
    return DB::transaction(function () use ($data) {
        $company = Company::firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier = Supplier::create($data);

        if ((float) $supplier->opening_balance > 0) {
            $journalEntry = $this->createOpeningBalanceJournalEntry(
                $company->id,
                $supplier
            );

            $supplier->update([
                'opening_balance_journal_entry_id' => $journalEntry->id,
            ]);
        }

        return $supplier->fresh(['company', 'creator']);
    });
}

    public function update($supplier, $data)
    {
        $supplier->update($data);
        return $supplier;
    }

    public function delete($supplier)
    {
        $supplier->delete();
    }
    public function restore(Supplier $supplier): void
    {
        if (!$supplier->trashed()) {
            abort(400, 'Supplier is not deleted.');
        }

        $supplier->restore();   
}
private function createOpeningBalanceJournalEntry(int $companyId, Supplier $supplier): JournalEntry
{
    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (
        ! $settings ||
        ! $settings->default_payable_account_id ||
        ! $settings->other_account_id
    ) {
        throw new RuntimeException(
            'Default payable account and other account must be configured before creating supplier opening balance.'
        );
    }

    $amount = (float) $supplier->opening_balance;

    $year = now()->format('Y');

    $lastEntryNumber = JournalEntry::query()
        ->where('company_id', $companyId)
        ->where('entry_number', 'like', "JV-{$year}-%")
        ->orderByDesc('id')
        ->value('entry_number');

    $nextNumber = 1;

    if ($lastEntryNumber) {
        $parts = explode('-', $lastEntryNumber);

        if (count($parts) === 3) {
            $nextNumber = ((int) $parts[2]) + 1;
        }
    }

    $entryNumber = sprintf(
        'JV-%s-%05d',
        $year,
        $nextNumber
    );

    $journalEntry = JournalEntry::create([
        'company_id'   => $companyId,
        'entry_number' => $entryNumber,
        'entry_date'   => now()->toDateString(),
        'total_debit'  => $amount,
        'total_credit' => $amount,
        'description'  => 'Supplier Opening Balance - ' . ($supplier->supplier_name_en ?? $supplier->supplier_name_ar),
        'status'       => 'posted',
        'created_by'   => auth('api')->id(),
    ]);

    $journalEntry->lines()->create([
        'company_id' => $companyId,
        'account_id' => $settings->other_account_id,
        'debit'      => $amount,
        'credit'     => 0,
        'note'       => 'Supplier Opening Balance',
    ]);

    $journalEntry->lines()->create([
        'company_id' => $companyId,
        'account_id' => $settings->default_payable_account_id,
        'debit'      => 0,
        'credit'     => $amount,
        'note'       => 'Supplier Opening Balance',
    ]);

    return $journalEntry;
}
}