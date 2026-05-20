<?php

namespace App\Services\BankAccount;

use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BankAccountService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return BankAccount::query()
            ->where('company_id', $companyId)
            ->with(['bank', 'chartAccount'])
            ->when($filters['bank_id'] ?? null, fn ($q, $v) => $q->where('bank_id', $v))
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): BankAccount
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $this->validateBank($companyId, (int) $data['bank_id']);

            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;

            return BankAccount::create($data)->load(['bank', 'chartAccount']);
        });
    }

    public function update(BankAccount $bankAccount, array $data): BankAccount
    {
        return DB::transaction(function () use ($bankAccount, $data) {
            if (isset($data['bank_id'])) {
                $this->validateBank($bankAccount->company_id, (int) $data['bank_id']);
            }

            $bankAccount->update($data);

            return $bankAccount->fresh(['bank', 'chartAccount']);
        });
    }

    public function delete(BankAccount $bankAccount): void
    {
        $bankAccount->delete();
    }

    private function validateBank(int $companyId, int $bankId): void
    {
        $bank = Bank::query()
            ->where('company_id', $companyId)
            ->where('id', $bankId)
            ->first();

        if (! $bank) {
            throw new InvalidArgumentException('Invalid bank selected.');
        }
    }
}