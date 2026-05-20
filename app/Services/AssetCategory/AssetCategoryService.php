<?php

namespace App\Services\AssetCategory;

use App\Models\AssetCategory;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetCategoryService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return AssetCategory::query()
            ->where('company_id', $companyId)
            ->with([
                'fixedAssetAccount',
                'accumulatedDepreciationAccount',
                'depreciationExpenseAccount',
                'capitalWorkInProgressAccount',
            ])
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v))
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): AssetCategory
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $this->validateDepreciationSettings($data);

            $this->validateAccount($companyId, $data['fixed_asset_account_id'], 'fixed_asset');
            $this->validateAccount($companyId, $data['accumulated_depreciation_account_id'], 'accumulated_depreciation');
            $this->validateAccount($companyId, $data['depreciation_expense_account_id'], 'depreciation');

            if ($data['depreciation_method'] !== 'written_down_value') {
                $data['depreciation_rate'] = null;
            }

            if ($data['depreciation_method'] === 'manual') {
                $data['frequency_month'] = null;
                $data['total_depreciation_count'] = null;
                $data['depreciation_posting_day'] = null;
                $data['depreciation_rate'] = null;
            }
if (! empty($data['capital_work_in_progress_account_id'])) {
    $this->validateAccount(
        $companyId,
        $data['capital_work_in_progress_account_id'],
        'capital_work_in_progress'
    );
}
            $data['company_id'] = $companyId;
            $data['created_by'] = $createdBy;
            $data['is_active'] = $data['is_active'] ?? true;

            return AssetCategory::create($data)->load([
                'fixedAssetAccount',
                'accumulatedDepreciationAccount',
                'depreciationExpenseAccount',
                'capitalWorkInProgressAccount',
            ]);
        });
    }

    public function update(AssetCategory $category, array $data): AssetCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $merged = array_merge($category->toArray(), $data);

            $this->validateDepreciationSettings($merged);

            if (isset($data['fixed_asset_account_id'])) {
                $this->validateAccount($category->company_id, $data['fixed_asset_account_id'], 'fixed_asset');
            }

            if (isset($data['accumulated_depreciation_account_id'])) {
                $this->validateAccount($category->company_id, $data['accumulated_depreciation_account_id'], 'accumulated_depreciation');
            }

            if (isset($data['depreciation_expense_account_id'])) {
                $this->validateAccount($category->company_id, $data['depreciation_expense_account_id'], 'depreciation');
            }

            $method = $data['depreciation_method'] ?? $category->depreciation_method;

            if ($method !== 'written_down_value') {
                $data['depreciation_rate'] = null;
            }

            if ($method === 'manual') {
                $data['frequency_month'] = null;
                $data['total_depreciation_count'] = null;
                $data['depreciation_posting_day'] = null;
                $data['depreciation_rate'] = null;
            }

            $category->update($data);

            return $category->fresh([
                'fixedAssetAccount',
                'accumulatedDepreciationAccount',
                'depreciationExpenseAccount',
                'capitalWorkInProgressAccount',
            ]);
        });
    }

    public function delete(AssetCategory $category): void
    {
        if ($category->assets()->exists()) {
            throw new InvalidArgumentException('لا يمكن حذف فئة مستخدمة في أصول.');
        }

        $category->delete();
    }

    private function validateAccount(int $companyId, int $accountId, string $type): void
    {
        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->where('account_type', $type)
            ->where('account_level', 'child')
            ->where('is_active', true)
            ->first();

        if (! $account) {
            throw new InvalidArgumentException("Invalid {$type} account selected.");
        }
    }

    private function validateDepreciationSettings(array $data): void
    {
        $method = $data['depreciation_method'] ?? null;

        if ($method === 'manual') {
            return;
        }

        if (empty($data['frequency_month'])) {
            throw new InvalidArgumentException('Frequency of depreciation is required.');
        }

        if (empty($data['total_depreciation_count'])) {
            throw new InvalidArgumentException('Total number of depreciation is required.');
        }

        if (empty($data['depreciation_posting_day'])) {
            throw new InvalidArgumentException('Depreciation posting day is required.');
        }

        if ($method === 'written_down_value' && empty($data['depreciation_rate'])) {
            throw new InvalidArgumentException('Rate of depreciation is required for written down value method.');
        }
    }
}