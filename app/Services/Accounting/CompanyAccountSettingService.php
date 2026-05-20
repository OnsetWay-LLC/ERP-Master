<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\CompanyAccountSetting;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompanyAccountSettingService
{
    private array $allowedTypes = [
        'default_bank_account_id' => ['bank'],
        'default_cash_account_id' => ['cash'],
        'default_receivable_account_id' => ['receivable'],
        'default_payable_account_id' => ['payable'],

        'default_income_account_id' => [
            'direct_income',
            'indirect_income',
            'income',
        ],

        'default_direct_income_account_id' => ['direct_income'],
        'default_indirect_income_account_id' => ['indirect_income'],

        'default_cogs_account_id' => ['cogs'],

        'default_direct_expense_account_id' => ['direct_expense'],
        'default_indirect_expense_account_id' => ['indirect_expense'],

        'default_inventory_account_id' => ['stock'],

        'default_sales_tax_account_id' => [
            'output_tax',
            'tax',
        ],

        'default_purchase_tax_account_id' => [
            'input_tax',
            'tax',
        ],

        'default_payment_discount_account_id' => [
            'direct_expense',
            'indirect_expense',
            'other',
        ],

        'accumulated_depreciation_account_id' => [
            'accumulated_depreciation',
        ],

        'depreciation_expense_account_id' => [
            'depreciation',
        ],

        'gain_loss_asset_disposal_account_id' => [
            'direct_income',
            'indirect_income',
            'direct_expense',
            'indirect_expense',
        ],

        'default_equity_account_id' => [
            'equity',
        ],

        'inventory_adjustment_account_id' => [
            'stock_adjustment',
        ],

        'other_account_id' => [
            'other',
        ],
    ];

    public function set(array $data, int $companyId): CompanyAccountSetting
    {
        return DB::transaction(function () use ($data, $companyId) {
            foreach ($data as $key => $accountId) {
                if (! $accountId) {
                    continue;
                }

                $allowedTypes = $this->allowedTypes[$key] ?? null;

                if (! $allowedTypes) {
                    throw new InvalidArgumentException("Invalid default account field: {$key}");
                }

                $account = ChartOfAccount::query()
                    ->where('company_id', $companyId)
                    ->where('id', $accountId)
                    ->whereIn('account_type', $allowedTypes)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->first();

                if (! $account) {
                    throw new InvalidArgumentException(
                        "Invalid account selected for {$key}. Account must be an active child account with one of these types: "
                        . implode(', ', $allowedTypes)
                    );
                }
            }

            return CompanyAccountSetting::updateOrCreate(
                ['company_id' => $companyId],
                $data
            );
        });
    }

    public function get(int $companyId): ?CompanyAccountSetting
    {
        return CompanyAccountSetting::query()
            ->where('company_id', $companyId)
            ->first();
    }

    public function allowedTypes(): array
    {
        return $this->allowedTypes;
    }
}