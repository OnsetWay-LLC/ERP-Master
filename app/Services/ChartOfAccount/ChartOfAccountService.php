<?php

namespace App\Services\ChartOfAccount;

use App\Constants\AccountTypes;
use App\Constants\ChartOfAccountCategories;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class ChartOfAccountService
{
  public function getAll(int $companyId, array $filters = []): Collection
{
    return ChartOfAccount::query()
        ->where('company_id', $companyId)
        ->when($filters['root_category'] ?? null, fn ($q, $v) => $q->where('root_category', $v))
        ->when($filters['root_categories'] ?? null, fn ($q, $v) => $q->whereIn('root_category', (array) $v))
        ->when($filters['sub_category'] ?? null, fn ($q, $v) => $q->where('sub_category', $v))
        ->when($filters['account_type'] ?? null, fn ($q, $v) => $q->where('account_type', $v))
        ->when($filters['account_level'] ?? null, fn ($q, $v) => $q->where('account_level', $v))
        ->orderBy('account_number')
        ->get();
}

    public function getTree(int $companyId): Collection
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('account_number')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): ChartOfAccount
    {
        $accountType = $data['account_type'];
        $accountLevel = $data['account_level'];
        $subCategory = $data['sub_category'];

        ChartOfAccountCategories::validateTypeWithSubCategory($accountType, $subCategory);

        $rootCategory = $accountType === AccountTypes::OTHER
            ? $data['root_category']
            : ChartOfAccountCategories::rootBySubCategory($subCategory);

        if ($accountLevel === 'child') {
            $parent = $this->findSelectedParent(
                $companyId,
                (int) $data['parent_account_id'],
                $accountType,
                $rootCategory,
                $subCategory
            );
        } else {
            $parent = $this->findCategoryParent(
                $companyId,
                $rootCategory,
                $subCategory
            );
        }

        return ChartOfAccount::create([
            'company_id' => $companyId,
            'parent_id' => $parent->id,

            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'account_number' => $data['account_number'],

            'root_category' => $rootCategory,
            'sub_category' => $subCategory,
            'account_type' => $accountType,
            'account_level' => $accountLevel,

            'is_active' => $data['is_active'] ?? true,
            'is_system' => false,
            'created_by' => $createdBy,
        ]);
    }

    public function update(ChartOfAccount $account, array $data): ChartOfAccount
    {
        if ($account->is_system) {
            throw new InvalidArgumentException('The selected account is a system account and cannot be modified.');
        }

        $accountType = $data['account_type'] ?? $account->account_type;
        $accountLevel = $data['account_level'] ?? $account->account_level;
        $subCategory = $data['sub_category'] ?? $account->sub_category;

        ChartOfAccountCategories::validateTypeWithSubCategory($accountType, $subCategory);

        $rootCategory = $accountType === AccountTypes::OTHER
            ? ($data['root_category'] ?? $account->root_category)
            : ChartOfAccountCategories::rootBySubCategory($subCategory);

        if ($accountLevel === 'child') {
            $parentId = $data['parent_account_id'] ?? $account->parent_id;

            $parent = $this->findSelectedParent(
                $account->company_id,
                (int) $parentId,
                $accountType,
                $rootCategory,
                $subCategory
            );
        } else {
            if ($account->children()->exists()) {
                throw new InvalidArgumentException('The selected account has child accounts and cannot be converted.');
            }

            $parent = $this->findCategoryParent(
                $account->company_id,
                $rootCategory,
                $subCategory
            );
        }

        $account->update([
            ...$data,
            'parent_id' => $parent->id,
            'root_category' => $rootCategory,
            'sub_category' => $subCategory,
            'account_type' => $accountType,
            'account_level' => $accountLevel,
        ]);

        return $account->fresh('children');
    }

    public function delete(ChartOfAccount $account): void
    {
        if ($account->is_system) {
            throw new InvalidArgumentException('The selected account is a system account and cannot be deleted.');
        }

        if ($account->children()->exists()) {
            throw new InvalidArgumentException('The selected account has child accounts and cannot be deleted.');
        }

        $account->delete();
    }

    private function findCategoryParent(
        int $companyId,
        string $rootCategory,
        string $subCategory
    ): ChartOfAccount {
        $parent = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('root_category', $rootCategory)
            ->where('sub_category', $subCategory)
            ->where('account_type', 'system')
            ->where('account_level', 'parent')
            ->where('is_system', true)
            ->first();

        if (! $parent) {
            throw new InvalidArgumentException('The selected classification does not exist in the chart of accounts.');
        }

        return $parent;
    }

    private function findSelectedParent(
        int $companyId,
        int $parentId,
        string $accountType,
        string $rootCategory,
        string $subCategory
    ): ChartOfAccount {
        $parent = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $parentId)
            ->where('account_level', 'parent')
            ->where('root_category', $rootCategory)
            ->where('sub_category', $subCategory)
            ->first();

        if (! $parent) {
            throw new InvalidArgumentException('The selected parent account is not valid.');
        }

        if ($accountType !== AccountTypes::OTHER && $parent->account_type !== $accountType) {
            throw new InvalidArgumentException('The selected parent account must be of the same type.');
        }

        return $parent;
    }
}