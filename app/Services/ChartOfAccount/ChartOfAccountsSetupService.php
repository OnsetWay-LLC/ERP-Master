<?php

namespace App\Services\ChartOfAccount;

use App\Models\ChartOfAccount;

class ChartOfAccountsSetupService
{
    public function createForCompany(int $companyId): void
    {
        $assets = $this->createAccount($companyId, null, 'Assets', 'الأصول', '1000', 'assets', null, 'system');

        $currentAssets = $this->createAccount($companyId, $assets->id, 'Current Assets', 'الأصول المتداولة', '1100', 'assets', 'current_assets', 'system');
        $fixedAssets = $this->createAccount($companyId, $assets->id, 'Fixed Assets', 'الأصول الثابتة', '1200', 'assets', 'fixed_assets', 'system');
        $fixedContra = $this->createAccount($companyId, $assets->id, 'Fixed Assets Contra', 'مجمع إهلاك الأصول', '1300', 'assets', 'fixed_assets_contra', 'system');
        $investment = $this->createAccount($companyId, $assets->id, 'Investment', 'الاستثمارات', '1400', 'assets', 'investment', 'system');
        $temporary = $this->createAccount($companyId, $assets->id, 'Temporary Accounts', 'الحسابات المؤقتة', '1500', 'assets', 'temporary_accounts', 'system');

        $this->createAccount($companyId, $currentAssets->id, 'Bank', 'البنك', '1110', 'assets', 'current_assets', 'bank');
        $this->createAccount($companyId, $currentAssets->id, 'Cash', 'الصندوق', '1120', 'assets', 'current_assets', 'cash');
        $this->createAccount($companyId, $currentAssets->id, 'Receivable', 'الذمم المدينة', '1130', 'assets', 'current_assets', 'receivable');
        $this->createAccount($companyId, $currentAssets->id, 'Stock', 'المخزون', '1140', 'assets', 'current_assets', 'stock');
        $this->createAccount($companyId, $currentAssets->id, 'Stock Adjustment', 'تسوية المخزون', '1150', 'assets', 'current_assets', 'stock_adjustment');
        $this->createAccount($companyId, $currentAssets->id, 'Input Tax', 'ضريبة المدخلات', '1160', 'assets', 'current_assets', 'input_tax');

        $this->createAccount($companyId, $fixedAssets->id, 'Fixed Asset', 'أصل ثابت', '1210', 'assets', 'fixed_assets', 'fixed_asset');
        $this->createAccount($companyId, $fixedAssets->id, 'Capital Work in Progress', 'أعمال رأسمالية تحت التنفيذ', '1220', 'assets', 'fixed_assets', 'capital_work_in_progress');
        $this->createAccount($companyId, $fixedAssets->id, 'Asset Recovery', 'استرداد أصل', '1230', 'assets', 'fixed_assets', 'asset_recovery');

        $this->createAccount($companyId, $fixedContra->id, 'Accumulated Depreciation', 'مجمع الإهلاك', '1310', 'assets', 'fixed_assets_contra', 'accumulated_depreciation');

        $this->createAccount($companyId, $investment->id, 'Investment Account', 'حساب استثمار', '1410', 'assets', 'investment', 'investment');
        $this->createAccount($companyId, $temporary->id, 'Temporary Account', 'حساب مؤقت', '1510', 'assets', 'temporary_accounts', 'temporary');

        $liabilities = $this->createAccount($companyId, null, 'Liabilities', 'الالتزامات', '2000', 'liabilities', null, 'system');

        $currentLiabilities = $this->createAccount($companyId, $liabilities->id, 'Current Liabilities', 'الالتزامات المتداولة', '2100', 'liabilities', 'current_liabilities', 'system');
        $nonCurrentLiabilities = $this->createAccount($companyId, $liabilities->id, 'Non-Current Liabilities', 'الالتزامات غير المتداولة', '2200', 'liabilities', 'non_current_liabilities', 'system');

        $this->createAccount($companyId, $currentLiabilities->id, 'Payable', 'الذمم الدائنة', '2110', 'liabilities', 'current_liabilities', 'payable');
        $this->createAccount($companyId, $currentLiabilities->id, 'Current Liability', 'التزام متداول', '2120', 'liabilities', 'current_liabilities', 'current_liability');
        $this->createAccount($companyId, $currentLiabilities->id, 'Output Tax', 'ضريبة المخرجات', '2130', 'liabilities', 'current_liabilities', 'output_tax');
        $this->createAccount($companyId, $currentLiabilities->id, 'Stock Received but not Billed', 'مخزون مستلم غير مفوتر', '2140', 'liabilities', 'current_liabilities', 'stock_received_not_billed');
        $this->createAccount($companyId, $currentLiabilities->id, 'Service Received but not Billed', 'خدمة مستلمة غير مفوترة', '2150', 'liabilities', 'current_liabilities', 'service_received_not_billed');

        $this->createAccount($companyId, $nonCurrentLiabilities->id, 'Liability', 'التزام غير متداول', '2210', 'liabilities', 'non_current_liabilities', 'liability');

        $equityRoot = $this->createAccount($companyId, null, 'Equity', 'حقوق الملكية', '3000', 'equity', null, 'system');

        $capitalStock = $this->createAccount($companyId, $equityRoot->id, 'Capital Stock', 'رأس المال', '3100', 'equity', 'capital_stock', 'system');
        $openingBalanceEquity = $this->createAccount($companyId, $equityRoot->id, 'Opening Balance Equity', 'حقوق ملكية افتتاحية', '3200', 'equity', 'opening_balance_equity', 'system');
        $retainedEarnings = $this->createAccount($companyId, $equityRoot->id, 'Retained Earnings', 'الأرباح المحتجزة', '3300', 'equity', 'retained_earnings', 'system');
        $revaluationSurplus = $this->createAccount($companyId, $equityRoot->id, 'Revaluation Surplus', 'فائض إعادة التقييم', '3400', 'equity', 'revaluation_surplus', 'system');
        $dividends = $this->createAccount($companyId, $equityRoot->id, 'Dividends Paid', 'الأرباح الموزعة', '3500', 'equity', 'dividends_paid', 'system');

        $this->createAccount($companyId, $capitalStock->id, 'Capital Account', 'حساب رأس المال', '3110', 'equity', 'capital_stock', 'capital');
        $this->createAccount($companyId, $openingBalanceEquity->id, 'Opening Balance Equity Account', 'حساب حقوق ملكية افتتاحية', '3210', 'equity', 'opening_balance_equity', 'equity');
        $this->createAccount($companyId, $retainedEarnings->id, 'Retained Earnings Account', 'حساب الأرباح المحتجزة', '3310', 'equity', 'retained_earnings', 'equity');
        $this->createAccount($companyId, $revaluationSurplus->id, 'Revaluation Surplus Account', 'حساب فائض إعادة التقييم', '3410', 'equity', 'revaluation_surplus', 'equity');
        $this->createAccount($companyId, $dividends->id, 'Dividends Paid Account', 'حساب الأرباح الموزعة', '3510', 'equity', 'dividends_paid', 'dividends_paid');

        $incomeRoot = $this->createAccount($companyId, null, 'Income', 'الإيرادات', '4000', 'income', null, 'system');

        $directIncome = $this->createAccount($companyId, $incomeRoot->id, 'Direct Income', 'إيرادات مباشرة', '4100', 'income', 'direct_income', 'system');
        $indirectIncome = $this->createAccount($companyId, $incomeRoot->id, 'Indirect Income', 'إيرادات غير مباشرة', '4200', 'income', 'indirect_income', 'system');

        $this->createAccount($companyId, $directIncome->id, 'Direct Income Account', 'حساب إيراد مباشر', '4110', 'income', 'direct_income', 'direct_income');
        $this->createAccount($companyId, $indirectIncome->id, 'Indirect Income Account', 'حساب إيراد غير مباشر', '4210', 'income', 'indirect_income', 'indirect_income');

        $expensesRoot = $this->createAccount($companyId, null, 'Expenses', 'المصاريف', '5000', 'expenses', null, 'system');

        $directExpenses = $this->createAccount($companyId, $expensesRoot->id, 'Direct Expenses', 'مصاريف مباشرة', '5100', 'expenses', 'direct_expenses', 'system');
        $valuationExpenses = $this->createAccount($companyId, $expensesRoot->id, 'Expenses Included in Valuation', 'مصاريف داخلة في التقييم', '5200', 'expenses', 'expenses_included_in_valuation', 'system');
        $indirectExpenses = $this->createAccount($companyId, $expensesRoot->id, 'Indirect Expenses', 'مصاريف غير مباشرة', '5300', 'expenses', 'indirect_expenses', 'system');
        $reimbursableExpenses = $this->createAccount($companyId, $expensesRoot->id, 'Indirect Expenses Reimbursable', 'مصاريف قابلة للاسترداد', '5400', 'expenses', 'indirect_expenses_reimbursable', 'system');
        $roundOffIncome = $this->createAccount($companyId, $expensesRoot->id, 'Round Off Income/Expense', 'فروقات التقريب', '5500', 'expenses', 'indirect_expenses_income', 'system');
        $roundOffOpening = $this->createAccount($companyId, $expensesRoot->id, 'Round Off Opening', 'فروقات افتتاحية', '5600', 'expenses', 'indirect_expenses_equity', 'system');

        $this->createAccount($companyId, $directExpenses->id, 'Cost of Goods Sold', 'تكلفة البضاعة المباعة', '5110', 'expenses', 'direct_expenses', 'cogs');
        $this->createAccount($companyId, $directExpenses->id, 'Direct Expense Account', 'حساب مصروف مباشر', '5120', 'expenses', 'direct_expenses', 'direct_expense');
        $this->createAccount($companyId, $valuationExpenses->id, 'Valuation Expense Account', 'حساب مصاريف التقييم', '5210', 'expenses', 'expenses_included_in_valuation', 'expenses_included_in_valuation');
        $this->createAccount($companyId, $indirectExpenses->id, 'Indirect Expense Account', 'حساب مصروف غير مباشر', '5310', 'expenses', 'indirect_expenses', 'indirect_expense');
        $this->createAccount($companyId, $indirectExpenses->id, 'Depreciation Expense Account', 'حساب مصروف الإهلاك', '5320', 'expenses', 'indirect_expenses', 'depreciation');
        $this->createAccount($companyId, $reimbursableExpenses->id, 'Chargeable Expense Account', 'حساب مصروف قابل للتحميل', '5410', 'expenses', 'indirect_expenses_reimbursable', 'chargeable');
        $this->createAccount($companyId, $roundOffIncome->id, 'Round Off Account', 'حساب التقريب', '5510', 'expenses', 'indirect_expenses_income', 'round_off');
        $this->createAccount($companyId, $roundOffOpening->id, 'Round Off Opening Account', 'حساب تقريب الرصيد الافتتاحي', '5610', 'expenses', 'indirect_expenses_equity', 'round_off_opening');
    }

    private function createAccount(
        int $companyId,
        ?int $parentId,
        string $nameEn,
        string $nameAr,
        string $accountNumber,
        string $rootCategory,
        ?string $subCategory,
        string $accountType
    ): ChartOfAccount {
        return ChartOfAccount::updateOrCreate(
            [
                'company_id' => $companyId,
                'account_number' => $accountNumber,
            ],
            [
                'parent_id' => $parentId,
                'name_en' => $nameEn,
                'name_ar' => $nameAr,
                'root_category' => $rootCategory,
                'sub_category' => $subCategory,
                'account_type' => $accountType,
                'account_level' => 'parent',
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }
}