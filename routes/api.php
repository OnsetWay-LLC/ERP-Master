<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Company\CompanyController;
use App\Http\Controllers\Api\Department\DepartmentController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\Supplier\SupplierController;
use App\Http\Controllers\Api\Warehouse\WarehouseController;
use App\Http\Controllers\Api\ItemGroup\ItemGroupController;
use App\Http\Controllers\Api\Item\ItemController;
use App\Http\Controllers\Api\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\Api\TaxTemplate\TaxTemplateController;
use App\Http\Controllers\Api\ChartOfAccount\ChartOfAccountController;
use App\Http\Controllers\Api\Accounting\CompanyAccountSettingController;
use App\Http\Controllers\Api\MaterialRequest\MaterialRequestController;
use App\Http\Controllers\Api\PurchaseReceipt\PurchaseReceiptController;
use App\Http\Controllers\Api\Shift\ShiftController;
use App\Http\Controllers\Api\FeesTemplate\FeesTemplateController;
use App\Http\Controllers\Api\AssetCategory\AssetCategoryController;
use App\Http\Controllers\Api\AssetItem\AssetItemController;
use App\Http\Controllers\Api\AssetLocation\AssetLocationController;
use App\Http\Controllers\Api\Asset\AssetController;
use App\Http\Controllers\Api\Bank\BankController;
use App\Http\Controllers\Api\BankAccount\BankAccountController;
use App\Http\Controllers\Api\StockEntry\StockEntryController;
use App\Http\Controllers\Api\Accounting\JournalEntryController;
use App\Http\Controllers\Api\Accounting\GeneralLedgerController;
use App\Http\Controllers\Api\BankReconciliation\BankReconciliationController;
use App\Http\Controllers\Api\Reports\TrialBalanceController;
use App\Http\Controllers\Api\Reports\ProfitLossController;

use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

   

    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::post('/forgot-password/send-code', [PasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
});
Route::prefix('companies')
    ->middleware(['auth:api', 'permission:screen.company,api', 'locale'])
    ->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
   Route::get('/lookups/countries', [CompanyController::class, 'countries']);

    });
  Route::prefix('departments')
    ->middleware(['auth:api', 'permission:screen.departments,api', 'locale'])
    ->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });
    Route::prefix('employees')
    ->middleware(['auth:api', 'permission:screen.employees,api', 'locale'])
    ->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });
   Route::prefix('users')
    ->middleware(['auth:api', 'permission:screen.users,api', 'locale'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/employee-by-national-id/{nationalId}', [UserController::class, 'employeeByNationalId']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });
    Route::prefix('roles')
    ->middleware(['auth:api', 'permission:screen.roles,api', 'locale'])
    ->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{role}', [RoleController::class, 'show']);
        Route::put('/{role}', [RoleController::class, 'update']);
        Route::delete('/{role}', [RoleController::class, 'destroy']);

        // لجلب كل permissions للفرونت
        Route::get('/permissions/all', [RoleController::class, 'permissions']);
    });
    Route::prefix('customers')
    ->middleware(['auth:api', 'permission:screen.customers,api', 'locale'])
    ->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{customer}', [CustomerController::class, 'show']);
        Route::put('/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
            Route::post('/{customer}/restore', [CustomerController::class, 'restore'])->withTrashed();
    });
    Route::prefix('suppliers')
    ->middleware(['auth:api', 'permission:screen.suppliers,api', 'locale'])
    ->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
        Route::post('/{supplier}/restore', [SupplierController::class, 'restore'])->withTrashed();
    });
    Route::prefix('warehouses')
    ->middleware(['auth:api', 'permission:screen.warehouses,api', 'locale'])
    ->group(function () {
        Route::get('/', [WarehouseController::class, 'index']);
        Route::post('/', [WarehouseController::class, 'store']);
        Route::get('/{warehouse}', [WarehouseController::class, 'show']);
        Route::put('/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy']);
    });
    Route::prefix('item-groups')
    ->middleware(['auth:api', 'permission:screen.item_groups,api', 'locale'])
    ->group(function () {
        Route::get('/', [ItemGroupController::class, 'index']);
        Route::post('/', [ItemGroupController::class, 'store']);
        Route::get('/{itemGroup}', [ItemGroupController::class, 'show']);
        Route::put('/{itemGroup}', [ItemGroupController::class, 'update']);
        Route::delete('/{itemGroup}', [ItemGroupController::class, 'destroy']);
        Route::post('/{itemGroup}/restore', [ItemGroupController::class, 'restore']) ->withTrashed();
    });

Route::prefix('items')
    ->middleware(['auth:api', 'permission:screen.items,api', 'locale'])
    ->group(function () {
        Route::get('/export/excel', [ItemController::class, 'exportExcel']);
        Route::post('/import/excel', [ItemController::class, 'importExcel']);
        Route::post('/scan-barcode', [ItemController::class, 'scanBarcode']);
        Route::get('/{id}/barcode/print', [ItemController::class, 'printBarcode']);

        Route::get('/', [ItemController::class, 'index']);
        Route::post('/', [ItemController::class, 'store']);
        Route::get('/{item}', [ItemController::class, 'show']);
        Route::put('/{item}', [ItemController::class, 'update']);
        Route::delete('/{item}', [ItemController::class, 'destroy']);
        Route::post('/{item}/restore', [ItemController::class, 'restore'])->withTrashed();
    });
    
    Route::prefix('accounting/chart-of-accounts')
    ->middleware(['auth:api', 'permission:screen.chart_of_accounts,api', 'locale'])
    ->group(function () {
    Route::get('/', [ChartOfAccountController::class, 'index']);
    Route::get('/tree', [ChartOfAccountController::class, 'tree']);
    Route::post('/', [ChartOfAccountController::class, 'store']);
    Route::get('/{id}', [ChartOfAccountController::class, 'show']);
    Route::put('/{id}', [ChartOfAccountController::class, 'update']);
    Route::delete('/{id}', [ChartOfAccountController::class, 'destroy']);
    Route::get('/account-types/{accountType}/sub-categories',
    [ChartOfAccountController::class, 'subCategoriesByType']
);
});
Route::prefix('accounting/default-accounts')
->middleware(['auth:api', 'permission:screen.default_accounts,api', 'locale'])
->group(function () {
    Route::get('/', [CompanyAccountSettingController::class, 'show']);
    Route::post('/', [CompanyAccountSettingController::class, 'store']);
    Route::put('/', [CompanyAccountSettingController::class, 'store']);
});
 Route::prefix('tax-templates')
    ->middleware(['auth:api', 'permission:screen.tax,api', 'locale'])
    ->group(function () {
           Route::get('/', [TaxTemplateController::class, 'index']);
    Route::post('/', [TaxTemplateController::class, 'store']);
    Route::get('/{id}', [TaxTemplateController::class, 'show']);
    Route::put('/{id}', [TaxTemplateController::class, 'update']);
    Route::delete('/{id}', [TaxTemplateController::class, 'destroy']);
    });

    Route::prefix('inventory/material-requests')
    ->middleware(['auth:api', 'permission:screen.material_requests,api', 'locale'])
    ->group(function () {
    Route::post('/', [MaterialRequestController::class, 'store']);
    Route::post('/{id}/submit', [MaterialRequestController::class, 'submit']);
    Route::delete('/{id}', [MaterialRequestController::class, 'destroy']);
Route::post('/{id}/cancel', [MaterialRequestController::class, 'cancel']);
});
Route::prefix('inventory/purchase-orders')
->middleware(['auth:api', 'permission:screen.purchase_orders,api', 'locale'])
->group(function () {
    Route::post('/', [PurchaseOrderController::class, 'store']);
    Route::post('/{id}/submit', [PurchaseOrderController::class, 'submit']);

});
Route::prefix('inventory/purchase-receipts')
->middleware(['auth:api', 'permission:screen.purchase_receipts,api', 'locale'])
->group(function () {
    Route::post('/', [PurchaseReceiptController::class, 'store']);
    Route::put('/{id}', [PurchaseReceiptController::class, 'update']);
});
Route::prefix('shifts')
    ->middleware(['auth:api', 'permission:screen.shifts,api', 'locale'])
    ->group(function () {
        Route::get('/', [ShiftController::class, 'index']);
        Route::post('/', [ShiftController::class, 'store']);
        Route::get('/{shift}', [ShiftController::class, 'show']);
        Route::put('/{shift}', [ShiftController::class, 'update']);
        Route::delete('/{shift}', [ShiftController::class, 'destroy']);
    });
    Route::middleware(['auth:api', 'permission:screen.fees_templates,api', 'locale'])->group(function () {
    Route::apiResource('fees-templates', FeesTemplateController::class);
});
    Route::middleware(['auth:api', 'permission:screen.assets', 'locale'])->group(function () {
    Route::apiResource('asset-categories', AssetCategoryController::class);
});
    Route::middleware(['auth:api', 'permission:screen.asset_items', 'locale'])->group(function () {
    Route::apiResource('asset-items', AssetItemController::class);
});
Route::middleware(['auth:api', 'permission:screen.asset_locations', 'locale'])->group(function () {
    Route::apiResource('asset-locations', AssetLocationController::class);
});
Route::middleware(['auth:api', 'permission:screen.assets', 'locale'])->group(function () {
    Route::apiResource('assets', AssetController::class);
});
Route::middleware(['auth:api', 'permission:screen.bank', 'locale'])->group(function () {
    Route::apiResource('banks', BankController::class);
});
Route::middleware(['auth:api', 'permission:screen.bank_accounts', 'locale'])->group(function () {
    Route::apiResource('bank-accounts', BankAccountController::class);
});
Route::middleware(['auth:api', 'permission:screen.stock_entries', 'locale'])->group(function () {
    Route::get('stock-entries', [StockEntryController::class, 'index']);
    Route::post('stock-entries', [StockEntryController::class, 'store']);
    Route::get('stock-entries/{id}', [StockEntryController::class, 'show']);

    Route::put('stock-entries/{id}', [StockEntryController::class, 'update']);

    Route::post('stock-entries/{id}/submit', [StockEntryController::class, 'submit']);
    Route::post('stock-entries/{id}/cancel', [StockEntryController::class, 'cancel']);
});
Route::middleware(['auth:api', 'locale'])->group(function () {
    Route::get('journal-entries/accounts/dropdown', [JournalEntryController::class, 'accountsDropdown'])
        ->middleware('permission:screen.journal_entries.view');

    Route::get('journal-entries', [JournalEntryController::class, 'index'])
        ->middleware('permission:screen.journal_entries.view');

    Route::post('journal-entries', [JournalEntryController::class, 'store'])
        ->middleware('permission:screen.journal_entries.create');

    Route::get('journal-entries/{id}', [JournalEntryController::class, 'show'])
        ->middleware('permission:screen.journal_entries.view');

    Route::put('journal-entries/{id}', [JournalEntryController::class, 'update'])
        ->middleware('permission:screen.journal_entries.update');

    Route::delete('journal-entries/{id}', [JournalEntryController::class, 'destroy'])
        ->middleware('permission:screen.journal_entries.delete');

    Route::post('journal-entries/{id}/submit', [JournalEntryController::class, 'submit'])
        ->middleware('permission:screen.journal_entries.submit');

    Route::post('journal-entries/{id}/cancel', [JournalEntryController::class, 'cancel'])
        ->middleware('permission:screen.journal_entries.cancel');
});
Route::middleware(['auth:api', 'locale'])->group(function () {
    Route::get('general-ledger/accounts/dropdown', [GeneralLedgerController::class, 'accountsDropdown'])
        ->middleware('permission:screen.general_ledger');

    Route::get('general-ledger', [GeneralLedgerController::class, 'index'])
        ->middleware('permission:screen.general_ledger');
        Route::get('general-ledger/export/excel', [GeneralLedgerController::class, 'exportExcel'])
    ->middleware('permission:screen.general_ledger');

Route::get('general-ledger/export/pdf', [GeneralLedgerController::class, 'exportPdf'])
    ->middleware('permission:screen.general_ledger');
});
Route::middleware(['auth:api', 'permission:screen.bank_reconciliation'])->group(function () {
    Route::post('bank-reconciliation/calculate', [BankReconciliationController::class, 'calculate']);
});
Route::middleware(['auth:api', 'permission:screen.trial_balance', 'locale'])->group(function () {
    Route::get('/reports/trial-balance', [TrialBalanceController::class, 'report']);
    Route::get('/reports/trial-balance/pdf', [TrialBalanceController::class, 'exportPdf']);
});
Route::middleware(['auth:api', 'permission:screen.profit_and_loss', 'locale'])->group(function () {
    Route::get('/reports/profit-loss', [ProfitLossController::class, 'report']);
    Route::get('/reports/profit-loss/pdf', [ProfitLossController::class, 'exportPdf']);
});