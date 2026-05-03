<?php

namespace App\Services\ChartOfAccount;

use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSetupService
{
    public function setupForCompany(int $companyId): void
    {
        DB::transaction(function () use ($companyId) {

            $roots = [
                [
                    'name_ar' => 'الأصول',
                    'name_en' => 'Assets',
                    'account_number' => '1000',
                    'root_category' => 'assets',
                    'children' => [
                        ['name_ar' => 'الأصول المتداولة', 'name_en' => 'Current Assets', 'account_number' => '1100', 'sub_category' => 'current_assets'],
                        ['name_ar' => 'الأصول الثابتة', 'name_en' => 'Fixed Assets', 'account_number' => '1200', 'sub_category' => 'fixed_assets'],
                        ['name_ar' => 'الاستثمارات', 'name_en' => 'Investment', 'account_number' => '1300', 'sub_category' => 'investment'],
                        ['name_ar' => 'الحسابات المؤقتة', 'name_en' => 'Temporary Accounts', 'account_number' => '1400', 'sub_category' => 'temporary_accounts'],
                    ],
                ],
                [
                    'name_ar' => 'الخصوم',
                    'name_en' => 'Liabilities',
                    'account_number' => '2000',
                    'root_category' => 'liabilities',
                    'children' => [
                        ['name_ar' => 'الخصوم المتداولة', 'name_en' => 'Current Liabilities', 'account_number' => '2100', 'sub_category' => 'current_liabilities'],
                        ['name_ar' => 'الخصوم غير المتداولة', 'name_en' => 'Non-Current Liabilities', 'account_number' => '2200', 'sub_category' => 'non_current_liabilities'],
                    ],
                ],
                [
                    'name_ar' => 'حقوق الملكية',
                    'name_en' => 'Equity',
                    'account_number' => '3000',
                    'root_category' => 'equity',
                    'children' => [
                        ['name_ar' => 'رأس المال', 'name_en' => 'Capital Stock', 'account_number' => '3100', 'sub_category' => 'capital_stock'],
                        ['name_ar' => 'الأرباح المحتجزة', 'name_en' => 'Retained Earnings', 'account_number' => '3200', 'sub_category' => 'retained_earnings'],
                        ['name_ar' => 'حقوق الملكية الافتتاحية', 'name_en' => 'Opening Balance Equity', 'account_number' => '3300', 'sub_category' => 'opening_balance_equity'],
                        ['name_ar' => 'توزيعات الأرباح', 'name_en' => 'Dividends Paid', 'account_number' => '3400', 'sub_category' => 'dividends_paid'],
                        ['name_ar' => 'فائض إعادة التقييم', 'name_en' => 'Revaluation Surplus', 'account_number' => '3500', 'sub_category' => 'revaluation_surplus'],
                    ],
                ],
                [
                    'name_ar' => 'الإيرادات',
                    'name_en' => 'Income',
                    'account_number' => '4000',
                    'root_category' => 'income',
                    'children' => [
                        ['name_ar' => 'إيرادات مباشرة', 'name_en' => 'Direct Income', 'account_number' => '4100', 'sub_category' => 'direct_income'],
                        ['name_ar' => 'إيرادات غير مباشرة', 'name_en' => 'Indirect Income', 'account_number' => '4200', 'sub_category' => 'indirect_income'],
                    ],
                ],
                [
                    'name_ar' => 'المصاريف',
                    'name_en' => 'Expenses',
                    'account_number' => '5000',
                    'root_category' => 'expenses',
                    'children' => [
                        ['name_ar' => 'مصاريف مباشرة', 'name_en' => 'Direct Expenses', 'account_number' => '5100', 'sub_category' => 'direct_expenses'],
                        ['name_ar' => 'مصاريف غير مباشرة', 'name_en' => 'Indirect Expenses', 'account_number' => '5200', 'sub_category' => 'indirect_expenses'],
                    ],
                ],
            ];

            foreach ($roots as $rootData) {
                $children = $rootData['children'];
                unset($rootData['children']);

                $root = ChartOfAccount::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'account_number' => $rootData['account_number'],
                    ],
                    [
                        'company_id' => $companyId,
                        'parent_id' => null,
                        'name_ar' => $rootData['name_ar'],
                        'name_en' => $rootData['name_en'],
                        'root_category' => $rootData['root_category'],
                        'sub_category' => null,
                        'account_type' => 'system',
                        'is_active' => true,
                        'is_system' => true,
                    ]
                );

                foreach ($children as $child) {
                    ChartOfAccount::updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'account_number' => $child['account_number'],
                        ],
                        [
                            'company_id' => $companyId,
                            'parent_id' => $root->id,
                            'name_ar' => $child['name_ar'],
                            'name_en' => $child['name_en'],
                            'root_category' => $rootData['root_category'],
                            'sub_category' => $child['sub_category'],
                            'account_type' => 'system',
                            'is_active' => true,
                            'is_system' => true,
                        ]
                    );
                }
            }
        });
    }
}