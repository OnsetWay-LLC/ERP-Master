<?php

namespace App\Services\Bank;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BankService
{
    public function getAll(int $companyId): Collection
    {
        return Bank::query()
            ->where('company_id', $companyId)
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): Bank
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {

            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;

            return Bank::create($data);
        });
    }

    public function update(Bank $bank, array $data): Bank
    {
        return DB::transaction(function () use ($bank, $data) {

            $bank->update($data);

            return $bank->fresh();
        });
    }

    public function delete(Bank $bank): void
    {
        $bank->delete();
    }
}