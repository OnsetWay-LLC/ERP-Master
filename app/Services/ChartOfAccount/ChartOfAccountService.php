<?php

namespace App\Services\ChartOfAccount;

use App\Constants\AccountTypes;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Services\ChartOfAccount\ChartOfAccountsSetupService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChartOfAccountService
{
    public function getAll(int $companyId, array $filters = [])
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->when(!empty($filters['root_category']), function ($query) use ($filters) {
                $query->where('root_category', $filters['root_category']);
            })
            ->when(!empty($filters['sub_category']), function ($query) use ($filters) {
                $query->where('sub_category', $filters['sub_category']);
            })
            ->when(!empty($filters['account_type']), function ($query) use ($filters) {
                $query->where('account_type', $filters['account_type']);
            })
            ->orderBy('account_number')
            ->get();
    }

    public function getTree(int $companyId): Collection
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with([
                'children.children.children',
            ])
            ->orderBy('account_number')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $userId = null): ChartOfAccount
{
    return DB::transaction(function () use ($data, $companyId, $userId) {

        // 👇 نحدد الأب مباشرة حسب نوع الحساب
        $parent = match ($data['account_type']) {

            // Assets → Current Assets
            'cash', 'bank', 'receivable', 'stock' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '1100') // Current Assets
                    ->first(),

            // Assets → Fixed Assets
            'fixed_asset', 'accumulated_depreciation' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '1200')
                    ->first(),

            // Liabilities → Current Liabilities
            'payable', 'tax' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '2100')
                    ->first(),

            // Income
            'direct_income' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '4100')
                    ->first(),

            'indirect_income' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '4200')
                    ->first(),

            // Expenses
            'direct_expense', 'cost_of_goods_sold' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '5100')
                    ->first(),

            'indirect_expense', 'depreciation' =>
                ChartOfAccount::where('company_id', $companyId)
                    ->where('account_number', '5200')
                    ->first(),

            default => null
        };

        if (!$parent) {
            throw new InvalidArgumentException('Parent account not found.');
        }

        return ChartOfAccount::create([
            'company_id' => $companyId,
            'parent_id' => $parent->id,
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'account_number' => $data['account_number'],
            'account_type' => $data['account_type'],
            'root_category' => $parent->root_category,
            'sub_category' => $parent->sub_category,
            'is_active' => $data['is_active'] ?? true,
            'is_system' => false,
            'created_by' => $userId,
        ]);
    });
}
    public function update(ChartOfAccount $account, array $data): ChartOfAccount
    {
        if ($account->is_system) {
            throw new InvalidArgumentException('لا يمكن تعديل حساب أساسي من الشجرة.');
        }

        if (isset($data['account_type'])) {
            $classification = $this->resolveClassification($data['account_type']);

            $parentId = $this->resolveParentId(
                $account->company_id,
                $classification['root_category'],
                $classification['sub_category']
            );

            if (!$parentId) {
                throw new InvalidArgumentException('لم يتم العثور على التصنيف الأساسي لهذا النوع من الحساب.');
            }

            $data['root_category'] = $classification['root_category'];
            $data['sub_category'] = $classification['sub_category'];
            $data['parent_id'] = $parentId;
        }

        $account->update($data);

        return $account->fresh();
    }

    public function delete(ChartOfAccount $account): void
    {
        if ($account->is_system) {
            throw new InvalidArgumentException('لا يمكن حذف حساب أساسي من الشجرة.');
        }

        if ($account->children()->exists()) {
            throw new InvalidArgumentException('لا يمكن حذف حساب لديه حسابات فرعية.');
        }

        $account->delete();
    }

    public function resolveClassification(string $accountType): array
    {
        return match ($accountType) {
            AccountTypes::CASH,
            AccountTypes::BANK,
            AccountTypes::RECEIVABLE,
            AccountTypes::STOCK => [
                'root_category' => 'assets',
                'sub_category' => 'current_assets',
            ],

            AccountTypes::FIXED_ASSET,
            AccountTypes::ACCUMULATED_DEPRECIATION => [
                'root_category' => 'assets',
                'sub_category' => 'fixed_assets',
            ],

            AccountTypes::PAYABLE,
            AccountTypes::TAX => [
                'root_category' => 'liabilities',
                'sub_category' => 'current_liabilities',
            ],

            AccountTypes::EQUITY => [
                'root_category' => 'equity',
                'sub_category' => 'opening_balance_equity',
            ],

            AccountTypes::DIRECT_INCOME => [
                'root_category' => 'income',
                'sub_category' => 'direct_income',
            ],

            AccountTypes::INDIRECT_INCOME => [
                'root_category' => 'income',
                'sub_category' => 'indirect_income',
            ],

            AccountTypes::DIRECT_EXPENSE,
            AccountTypes::COST_OF_GOODS_SOLD => [
                'root_category' => 'expenses',
                'sub_category' => 'direct_expenses',
            ],

            AccountTypes::INDIRECT_EXPENSE,
            AccountTypes::DEPRECIATION => [
                'root_category' => 'expenses',
                'sub_category' => 'indirect_expenses',
            ],

            default => throw new InvalidArgumentException('نوع الحساب غير صحيح.'),
        };
    }

  private function resolveParentId(int $companyId, string $rootCategory, ?string $subCategory): ?int
{
    $map = [
        'current_assets' => '1100',
        'fixed_assets' => '1200',
        'investment' => '1300',
        'temporary_accounts' => '1400',

        'current_liabilities' => '2100',
        'non_current_liabilities' => '2200',

        'capital_stock' => '3100',
        'retained_earnings' => '3200',
        'opening_balance_equity' => '3300',
        'dividends_paid' => '3400',
        'revaluation_surplus' => '3500',

        'direct_income' => '4100',
        'indirect_income' => '4200',

        'direct_expenses' => '5100',
        'indirect_expenses' => '5200',
    ];

    if (!$subCategory || !isset($map[$subCategory])) {
        return null;
    }

    return ChartOfAccount::query()
        ->where('company_id', $companyId)
        ->where('account_number', $map[$subCategory])
        ->where('is_system', true)
        ->value('id');
}
}