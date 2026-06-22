<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'screen.company',
            'screen.departments',
            'screen.roles',
            'screen.users',
            'screen.employees',

            'screen.chart_of_accounts',
            'screen.default_accounts',
           'screen.journal_entries.view',
           'screen.journal_entries.create',
           'screen.journal_entries.update',
           'screen.journal_entries.delete',
           'screen.journal_entries.submit',
           'screen.journal_entries.cancel',
            'screen.general_ledger',
            'screen.bank_reconciliation',
            'screen.trial_balance',
            'screen.balance_sheet',
            'screen.profit_and_loss',
            'screen.tax',
            'screen.fees_templates',
            'screen.assets',
            'screen.asset_items',
            'screen.asset_locations',
            'screen.bank',
            'screen.bank_accounts',
            'screen.shifts',
            'screen.discount_settings',
            'screen.customers',
            'screen.sales_orders',
            'screen.pick_lists',
            'screen.delivery_notes',
            'screen.monthly_distributions',
            'screen.sales_persons',
            'screen.purchase_receipts',
            'screen.sales_invoices',
            'screen.sales_person_performance_report',
            'screen.sales_payments',
            'screen.sales_returns',
            'screen.financial_reports',
            'screen.AccountsReceivableReport',
            'screen.SalesRegisterReport',
            'screen.ItemWiseSalesRegisterReport',
            'screen.sales_payment_summary_report',
            'screen.suppliers',
            'screen.purchase_orders',
            'screen.purchase_invoices',
            'screen.purchase_reports',
            'screen.vacations',
            'screen.payroll_tax_settings',
            'screen.warehouses',
            'screen.item_groups',
            'screen.items',
            'screen.stock_entries',
            'screen.material_requests',
            'screen.stock_ledger',
            'screen.purchase_payments',
            'screen.purchase_returns',
            'screen.supplier_ledger_reports',
            'screen.ItemWisePurchaseRegisterReport',
            'screen.asset-capitalizations',
            'screen.asset_value_adjustments',
            'screen.asset_repairs',
            'screen.asset_Sale',
            'screen.asset_depreciation_entries',
            'permission:screen.assets.scrappings',
            'permission:screen.fixed.asset.register.report',
            'screen.account_closing',
            'screen.tax_declaration_settings',
           'screen.tax_declaration_reports',
           'screen.gross_profit_report'

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}