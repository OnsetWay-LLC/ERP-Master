<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $cfo = Role::findByName('CFO', 'api');
        $accountantChief = Role::findByName('Accountant Chief', 'api');
        $accountantSub = Role::findByName('Accountant Sub', 'api');
        $salesOfficer = Role::findByName('Sales Officer', 'api');
        $purchaseOfficer = Role::findByName('Purchase Officer', 'api');
        $stockOfficer = Role::findByName('Stock Officer', 'api');
        $hr = Role::findByName('HR', 'api');

        $cfo->syncPermissions([
            'screen.company',
            'screen.departments',
            'screen.roles',
            'screen.users',
            'screen.employees',

            'screen.chart_of_accounts',
            'screen.default_accounts',
            'screen.journal_entries',
            'screen.general_ledger',
            'screen.trial_balance',
            'screen.balance_sheet',
            'screen.profit_and_loss',
            'screen.tax',
            'screen.assets',
            'screen.bank',

            'screen.customers',
            'screen.sales_orders',
            'screen.sales_invoices',

            'screen.suppliers',
            'screen.purchase_orders',
            'screen.purchase_invoices',

            'screen.warehouses',
            'screen.item_groups',
            'screen.items',
            'screen.stock_entries',
            'screen.material_requests',
            'screen.stock_ledger',
        ]);

        $accountantChief->syncPermissions([
            'screen.chart_of_accounts',
            'screen.default_accounts',
            'screen.journal_entries',
            'screen.general_ledger',
            'screen.trial_balance',
            'screen.balance_sheet',
            'screen.profit_and_loss',
            'screen.tax',
            'screen.assets',
            'screen.bank',
            'screen.sales_invoices',
            'screen.purchase_invoices',
        ]);

        $accountantSub->syncPermissions([
            'screen.sales_invoices',
            'screen.purchase_invoices',
            'screen.journal_entries',
            'screen.general_ledger',
        ]);

        $salesOfficer->syncPermissions([//مدير مبيعات
            'screen.customers',
            'screen.sales_orders',
            'screen.warehouses',
            'screen.items',
        ]);

        $purchaseOfficer->syncPermissions([
            'screen.suppliers',
            'screen.purchase_orders',
            'screen.material_requests',
        ]);

        $stockOfficer->syncPermissions([
            'screen.warehouses',
            'screen.item_groups',
            'screen.items',
            'screen.stock_entries',
            'screen.material_requests',
            'screen.stock_ledger',
        ]);

        $hr->syncPermissions([
            'screen.employees',
        ]);
    }
}