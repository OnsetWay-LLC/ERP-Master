<?php

namespace App\Constants;

use InvalidArgumentException;

class ChartOfAccountCategories
{
    public static function roots(): array
    {
        return [
            'assets',
            'liabilities',
            'equity',
            'income',
            'expenses',
        ];
    }

    public static function subCategories(): array
    {
        return [
            'current_assets',
            'fixed_assets',
            'fixed_assets_contra',
            'investment',
            'temporary_accounts',

            'current_liabilities',
            'non_current_liabilities',

            'capital_stock',
            'opening_balance_equity',
            'retained_earnings',
            'revaluation_surplus',
            'dividends_paid',

            'direct_income',
            'indirect_income',

            'direct_expenses',
            'expenses_included_in_valuation',
            'indirect_expenses',
            'indirect_expenses_reimbursable',
            'indirect_expenses_income',
            'indirect_expenses_equity',
        ];
    }

    public static function allowedSubCategoriesByType(): array
    {
        return [
            // Assets
            'bank' => ['current_assets'],
            'cash' => ['current_assets'],
            'receivable' => ['current_assets'],
            'stock' => ['current_assets'],
            'stock_adjustment' => ['current_assets'],
            'input_tax' => ['current_assets'],

            'fixed_asset' => ['fixed_assets'],
            'capital_work_in_progress' => ['fixed_assets'],
            'asset_recovery' => ['fixed_assets'],
            'accumulated_depreciation' => ['fixed_assets_contra'],

            'investment' => ['investment'],
            'temporary' => ['temporary_accounts'],

            // Liabilities
            'payable' => ['current_liabilities'],
            'current_liability' => ['current_liabilities'],
            'output_tax' => ['current_liabilities'],
            'stock_received_not_billed' => ['current_liabilities'],
            'service_received_not_billed' => ['current_liabilities'],
            'liability' => ['non_current_liabilities'],

            // Equity
            'capital' => ['capital_stock'],
            'equity' => [
                'opening_balance_equity',
                'retained_earnings',
                'revaluation_surplus',
            ],
            'dividends_paid' => ['dividends_paid'],

            // Income
            'direct_income' => ['direct_income'],
            'indirect_income' => ['indirect_income'],

            // Expenses
            'cogs' => ['direct_expenses'],
            'direct_expense' => ['direct_expenses'],
            'expenses_included_in_valuation' => ['expenses_included_in_valuation'],
            'indirect_expense' => ['indirect_expenses'],
            'depreciation' => ['indirect_expenses'],
            'chargeable' => ['indirect_expenses_reimbursable'],
            'round_off' => ['indirect_expenses_income'],
            'round_off_opening' => ['indirect_expenses_equity'],
        ];
    }

    public static function rootBySubCategory(string $subCategory): string
    {
        return match ($subCategory) {
            'current_assets',
            'fixed_assets',
            'fixed_assets_contra',
            'investment',
            'temporary_accounts' => 'assets',

            'current_liabilities',
            'non_current_liabilities' => 'liabilities',

            'capital_stock',
            'opening_balance_equity',
            'retained_earnings',
            'revaluation_surplus',
            'dividends_paid' => 'equity',

            'direct_income',
            'indirect_income' => 'income',

            'direct_expenses',
            'expenses_included_in_valuation',
            'indirect_expenses',
            'indirect_expenses_reimbursable',
            'indirect_expenses_income',
            'indirect_expenses_equity' => 'expenses',

            default => throw new InvalidArgumentException('Invalid sub category.'),
        };
    }

    public static function validateTypeWithSubCategory(
        string $accountType,
        string $subCategory
    ): void {
        if ($accountType === AccountTypes::OTHER) {
            return;
        }

        $allowed = self::allowedSubCategoriesByType()[$accountType] ?? null;

        if (! $allowed || ! in_array($subCategory, $allowed, true)) {
            throw new InvalidArgumentException('Account type does not match selected sub category.');
        }
    }
    public static function subCategoriesByAccountType(string $accountType): array
{
    if ($accountType === AccountTypes::OTHER) {
        return self::subCategories();
    }

    return self::allowedSubCategoriesByType()[$accountType] ?? [];
}
}