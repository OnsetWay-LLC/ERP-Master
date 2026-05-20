<?php

namespace App\Services\FeesTemplate;

use App\Models\ChartOfAccount;
use App\Models\FeesTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeesTemplateService
{
    private array $allowedAccountTypes = [
        'direct_income',
        'indirect_income',
        'indirect_expense',
        'expenses_included_in_valuation',
    ];

    public function getAll(int $companyId, array $filters = []): LengthAwarePaginator
    {
        return FeesTemplate::query()
            ->where('company_id', $companyId)
            ->with('account')
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', request()->boolean('is_active')))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): FeesTemplate
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $this->validateAccount($companyId, (int) $data['account_id']);
            $this->validateTypeValues($data);

            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;
            $data['is_active'] = $data['is_active'] ?? true;

            if ($data['type'] === 'percentage') {
                $data['amount'] = null;
            }

            if ($data['type'] === 'fixed_amount') {
                $data['fees_rate'] = null;
            }

            return FeesTemplate::create($data)->load('account');
        });
    }

    public function update(FeesTemplate $feesTemplate, array $data): FeesTemplate
    {
        return DB::transaction(function () use ($feesTemplate, $data) {
            if (isset($data['account_id'])) {
                $this->validateAccount($feesTemplate->company_id, (int) $data['account_id']);
            }

            $merged = array_merge($feesTemplate->toArray(), $data);
            $this->validateTypeValues($merged);

            if (($data['type'] ?? $feesTemplate->type) === 'percentage') {
                $data['amount'] = null;
            }

            if (($data['type'] ?? $feesTemplate->type) === 'fixed_amount') {
                $data['fees_rate'] = null;
            }

            $feesTemplate->update($data);

            return $feesTemplate->fresh('account');
        });
    }

    public function delete(FeesTemplate $feesTemplate): void
    {
        $feesTemplate->delete();
    }

    private function validateAccount(int $companyId, int $accountId): void
    {
        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->whereIn('account_type', $this->allowedAccountTypes)
            ->first();

        if (! $account) {
            throw new InvalidArgumentException(
                'Invalid account selected. Account must be active child account of allowed fees account types.'
            );
        }
    }

    private function validateTypeValues(array $data): void
    {
        $type = $data['type'] ?? null;

        if ($type === 'percentage' && empty($data['fees_rate'])) {
            throw new InvalidArgumentException('Fees rate is required for percentage type.');
        }

        if ($type === 'fixed_amount' && empty($data['amount'])) {
            throw new InvalidArgumentException('Amount is required for fixed amount type.');
        }
    }
}