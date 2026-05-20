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

            'screen.customers',
            'screen.sales_orders',
            'screen.purchase_receipts',
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}