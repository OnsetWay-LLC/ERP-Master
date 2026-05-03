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
use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::post('/forgot-password', [PasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
});

Route::prefix('companies')
    ->middleware(['auth:api', 'permission:screen.company,api'])
    ->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
   Route::get('/lookups/countries', [CompanyController::class, 'countries']);

    });
  Route::prefix('departments')
    ->middleware(['auth:api', 'permission:screen.departments,api'])
    ->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });
    Route::prefix('employees')
    ->middleware(['auth:api', 'permission:screen.employees,api'])
    ->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });
   Route::prefix('users')
    ->middleware(['auth:api', 'permission:screen.users,api'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/employee-by-national-id/{nationalId}', [UserController::class, 'employeeByNationalId']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });
    Route::prefix('roles')
    ->middleware(['auth:api', 'permission:screen.roles,api'])
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
    ->middleware(['auth:api', 'permission:screen.customers,api'])
    ->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{customer}', [CustomerController::class, 'show']);
        Route::put('/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
    });
    Route::prefix('suppliers')
    ->middleware(['auth:api', 'permission:screen.suppliers,api'])
    ->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });
    Route::prefix('warehouses')
    ->middleware(['auth:api', 'permission:screen.warehouses,api'])
    ->group(function () {
        Route::get('/', [WarehouseController::class, 'index']);
        Route::post('/', [WarehouseController::class, 'store']);
        Route::get('/{warehouse}', [WarehouseController::class, 'show']);
        Route::put('/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy']);
    });
    Route::prefix('item-groups')
    ->middleware(['auth:api', 'permission:screen.item_groups,api'])
    ->group(function () {
        Route::get('/', [ItemGroupController::class, 'index']);
        Route::post('/', [ItemGroupController::class, 'store']);
        Route::get('/{itemGroup}', [ItemGroupController::class, 'show']);
        Route::put('/{itemGroup}', [ItemGroupController::class, 'update']);
        Route::delete('/{itemGroup}', [ItemGroupController::class, 'destroy']);
    });

Route::prefix('items')
    ->middleware(['auth:api', 'permission:screen.items,api'])
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
    });
    
    Route::prefix('accounting/chart-of-accounts')
    ->middleware(['auth:api', 'permission:screen.chart_of_accounts,api'])
    ->group(function () {
    Route::get('/', [ChartOfAccountController::class, 'index']);
    Route::get('/tree', [ChartOfAccountController::class, 'tree']);
    Route::post('/', [ChartOfAccountController::class, 'store']);
    Route::get('/{id}', [ChartOfAccountController::class, 'show']);
    Route::put('/{id}', [ChartOfAccountController::class, 'update']);
    Route::delete('/{id}', [ChartOfAccountController::class, 'destroy']);
});
Route::prefix('accounting/default-accounts')
->middleware(['auth:api', 'permission:screen.default_accounts,api'])
->group(function () {
    Route::get('/', [CompanyAccountSettingController::class, 'show']);
    Route::post('/', [CompanyAccountSettingController::class, 'store']);
    Route::put('/', [CompanyAccountSettingController::class, 'store']);
});
 Route::prefix('tax-templates')
    ->middleware(['auth:api', 'permission:screen.tax,api'])
    ->group(function () {
           Route::get('/', [TaxTemplateController::class, 'index']);
    Route::post('/', [TaxTemplateController::class, 'store']);
    Route::get('/{id}', [TaxTemplateController::class, 'show']);
    Route::put('/{id}', [TaxTemplateController::class, 'update']);
    Route::delete('/{id}', [TaxTemplateController::class, 'destroy']);
    });

    Route::prefix('inventory/material-requests')
    ->middleware(['auth:api', 'permission:screen.material_requests,api'])
    ->group(function () {
    Route::post('/', [MaterialRequestController::class, 'store']);
    Route::post('/{id}/submit', [MaterialRequestController::class, 'submit']);
    Route::delete('/{id}', [MaterialRequestController::class, 'destroy']);
Route::post('/{id}/cancel', [MaterialRequestController::class, 'cancel']);
});
Route::prefix('inventory/purchase-orders')
->middleware(['auth:api', 'permission:screen.purchase_orders,api'])
->group(function () {
    Route::post('/', [PurchaseOrderController::class, 'store']);
    Route::post('/{id}/submit', [PurchaseOrderController::class, 'submit']);

});