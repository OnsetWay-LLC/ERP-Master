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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}