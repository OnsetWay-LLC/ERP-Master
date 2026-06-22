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
use App\Http\Controllers\Api\PayrollTaxSetting\PayrollTaxSettingController;
use App\Http\Controllers\Api\EmployeeLeave\EmployeeLeaveController;
use App\Http\Controllers\Api\SalesOrder\SalesOrderController;
use App\Http\Controllers\Api\SalesInvoice\SalesInvoiceController;
use App\Http\Controllers\Api\DiscountSetting\DiscountSettingController;
use App\Http\Controllers\Api\DiscountApproval\DiscountApprovalController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\PickList\PickListController;
use App\Http\Controllers\Api\DeliveryNote\DeliveryNoteController;
use App\Http\Controllers\Api\MonthlyDistribution\MonthlyDistributionController;
use App\Http\Controllers\Api\SalesPerson\SalesPersonController;
use App\Http\Controllers\Api\Reports\SalesPersonPerformanceReportController;
use App\Http\Controllers\Api\Payroll\PayrollController;
use App\Http\Controllers\Api\StockEntry\StockReportController;
use App\Http\Controllers\Api\Reports\CustomerLedgerReportController;
use App\Http\Controllers\Api\SalesPayment\SalesPaymentController;
use App\Http\Controllers\Api\SalesReturn\SalesReturnController;
use App\Http\Controllers\Api\Reports\AccountsReceivableReportController;
use App\Http\Controllers\Api\Reports\SalesRegisterReportController;
use App\Http\Controllers\Api\Reports\ItemWiseSalesRegisterReportController;
use App\Http\Controllers\Api\Reports\SalesPaymentSummaryReportController;
use App\Http\Controllers\Api\purchaseInvoice\PurchaseInvoiceController;
use App\Http\Controllers\Api\Reports\PurchaseRegisterReportController;
use App\Http\Controllers\Api\PaymentEntry\PaymentEntryController;
use App\Http\Controllers\Api\PurchaseReturn\PurchaseReturnController;
use App\Http\Controllers\Api\Reports\SupplierLedgerReportController;
use App\Http\Controllers\Api\Reports\ItemWisePurchaseRegisterReportController;
use App\Http\Controllers\Api\AssetCapitalization\AssetCapitalizationController;
use App\Http\Controllers\Api\AssetValueAdjustment\AssetValueAdjustmentController;
use App\Http\Controllers\Api\AssetRepair\AssetRepairController;
use App\Http\Controllers\Api\AssetSale\AssetSaleController;
use App\Http\Controllers\Api\AssetScrapping\AssetScrappingController;
use App\Http\Controllers\Api\Reports\FixedAssetRegisterReportController;
use App\Http\Controllers\Api\Reports\AssetDepreciationLedgerReportController;
use App\Http\Controllers\Api\Reports\AssetDepreciationBalanceReportController;
use App\Http\Controllers\Api\FinancialYear\FinancialYearController;
use App\Http\Controllers\Api\TaxDeclarationSetting\TaxDeclarationSettingController;
use App\Http\Controllers\Api\Reports\TaxDeclarationReportController;

use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

   

    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::post('/forgot-password/send-code', [PasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
});
//companies routes
Route::middleware(['auth:api', 'permission:screen.company,api', 'locale'])
    ->group(function () {

        Route::get('/companies/lookups/countries', [CompanyController::class, 'countries']);

        Route::apiResource('/companies', CompanyController::class)
            ->except(['destroy']);
    });
Route::post(
    'payrolls/generate',
    [PayrollController::class, 'generate']
);
Route::prefix('payroll-tax-settings')
    ->middleware(['auth:api', 'permission:screen.payroll_tax_settings,api', 'locale'])
    ->group(function () {
        Route::get('/', [PayrollTaxSettingController::class, 'show']);
        Route::post('/', [PayrollTaxSettingController::class, 'storeOrUpdate']);
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
        Route::post('/{employee}/restore', [EmployeeController::class, 'restore'])->withTrashed();
    });
   Route::prefix('employee-leaves')
    ->middleware(['auth:api', 'permission:screen.employees,api', 'locale'])
    ->group(function () {
      Route::get('/employees/search', [EmployeeLeaveController::class, 'searchEmployees']);
Route::get('/employees/{employee}/leave-options', [EmployeeLeaveController::class, 'leaveOptions']);

Route::get('/', [EmployeeLeaveController::class, 'index']);
Route::post('/', [EmployeeLeaveController::class, 'store']);
Route::get('/{employeeLeave}', [EmployeeLeaveController::class, 'show']);
    });
   Route::prefix('users')
    ->middleware(['auth:api', 'permission:screen.users,api', 'locale'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/employee-by-national-id/{nationalId}', [UserController::class, 'employeeByNationalId']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{user}', [UserController::class, 'update']);
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
    Route::put('/', [CompanyAccountSettingController::class, 'update']);
});
 Route::prefix('tax-templates')
    ->middleware(['auth:api', 'permission:screen.tax,api', 'locale'])
    ->group(function () {
           Route::get('/', [TaxTemplateController::class, 'index']);
    Route::post('/', [TaxTemplateController::class, 'store']);
    Route::get('/{id}', [TaxTemplateController::class, 'show']);
    Route::put('/{id}', [TaxTemplateController::class, 'update']);
    Route::delete('/{id}', [TaxTemplateController::class, 'destroy']);
    Route::post('/{id}/restore', [TaxTemplateController::class, 'restore'])->withTrashed();
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
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
        Route::post('/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
        Route::post('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

});
Route::prefix('inventory/purchase-receipts')
->middleware(['auth:api', 'permission:screen.purchase_receipts,api', 'locale'])
->group(function () {
        Route::get('/',[PurchaseReceiptController::class, 'index']);
        Route::post('/', [PurchaseReceiptController::class, 'store']);
        Route::get('/{purchaseReceipt}', [PurchaseReceiptController::class, 'show']);
        Route::put('/{purchaseReceipt}', [PurchaseReceiptController::class, 'update']);
        Route::post('/{purchaseReceipt}/submit', [PurchaseReceiptController::class, 'submit']);
        Route::post('/{purchaseReceipt}/cancel', [PurchaseReceiptController::class, 'cancel']);
    });
    Route::middleware(['auth:api', 'permission:screen.purchase_invoices', 'locale'])->group(function () {
    Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index']);
    Route::post('/purchase-invoices', [PurchaseInvoiceController::class, 'store']);
    Route::get('/purchase-invoices/accounts', [PurchaseInvoiceController::class, 'accounts']);
    Route::get('/purchase-invoices/{purchaseInvoice}', [PurchaseInvoiceController::class, 'show']);
    Route::post('/purchase-invoices/{purchaseInvoice}/submit', [PurchaseInvoiceController::class, 'submit']);
    Route::put( '/purchase-invoices/{id}', [PurchaseInvoiceController::class, 'update']);
    Route::post('/purchase-invoices/{purchaseInvoice}/cancel', [PurchaseInvoiceController::class, 'cancel']);
    Route::get('/purchase-invoices/{purchaseInvoice}/pdf', [PurchaseInvoiceController::class, 'pdf']);
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
    Route::get(
    '/assets/{id}/depreciation-journal-template',
    [AssetController::class, 'depreciationJournalTemplate']
);
});
    Route::middleware(['auth:api', 'permission:screen.asset_items', 'locale'])->group(function () {
    Route::apiResource('asset-items', AssetItemController::class);
});
Route::middleware(['auth:api', 'permission:screen.asset_locations', 'locale'])->group(function () {
    Route::apiResource('asset-locations', AssetLocationController::class);
});
Route::middleware(['auth:api', 'permission:screen.assets', 'locale'])->group(function () {
    Route::apiResource('assets', AssetController::class);
    Route::post('assets/{asset}/submit', [AssetController::class, 'submit']);
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
    Route::get(
    'stock-balance',
    [StockReportController::class, 'stockBalance']
);
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
Route::middleware(['auth:api', 'permission:screen.sales_orders', 'locale'])
    ->prefix('sales-orders')
    ->group(function () {
        Route::get('/', [SalesOrderController::class, 'index']);
        Route::post('/', [SalesOrderController::class, 'store']);
        Route::get('/{salesOrder}', [SalesOrderController::class, 'show']);
        Route::put('/{salesOrder}', [SalesOrderController::class, 'update']);
        Route::delete('/{salesOrder}', [SalesOrderController::class, 'destroy']);

        Route::post('/{salesOrder}/submit', [SalesOrderController::class, 'submit']);
        Route::post('/{salesOrder}/cancel', [SalesOrderController::class, 'cancel']);
    });
Route::middleware(['auth:api', 'permission:screen.pick_lists', 'locale'])
    ->prefix('pick-lists')
    ->group(function () {
        Route::get('/', [PickListController::class, 'index']);
        Route::get('/{pickList}', [PickListController::class, 'show']);
        Route::post('/{pickList}/cancel', [PickListController::class, 'cancel']);
    });
    Route::middleware(['auth:api', 'permission:screen.delivery_notes', 'locale'])
    ->prefix('delivery-notes')
    ->group(function () {
        Route::get('/', [DeliveryNoteController::class, 'index']);
        Route::get('/{deliveryNote}', [DeliveryNoteController::class, 'show']);

        Route::post('/from-pick-list/{pickList}', [DeliveryNoteController::class, 'storeFromPickList']);

        Route::post('/{deliveryNote}/submit', [DeliveryNoteController::class, 'submit']);
        Route::post('/{deliveryNote}/cancel', [DeliveryNoteController::class, 'cancel']);
    });

    Route::middleware(['auth:api', 'permission:screen.monthly_distributions', 'locale'])
        ->prefix('monthly-distributions')
        ->group(function () {
            Route::get('/', [MonthlyDistributionController::class, 'index']);
            Route::post('/', [MonthlyDistributionController::class, 'store']);
            Route::get('/{monthlyDistribution}', [MonthlyDistributionController::class, 'show']);
            Route::put('/{monthlyDistribution}', [MonthlyDistributionController::class, 'update']);
            Route::delete('/{monthlyDistribution}', [MonthlyDistributionController::class, 'destroy']);
        });
        Route::prefix('sales-persons')
    ->middleware(['auth:api', 'permission:screen.sales_persons', 'locale'])
    ->group(function () {

        Route::get('/', [SalesPersonController::class, 'index']);
        Route::post('/', [SalesPersonController::class, 'store']);
        Route::post('post-commission', [SalesPersonController::class, 'postCommission']);
        Route::post('{id}/restore', [SalesPersonController::class, 'restore'])->withTrashed();
        Route::get('{salesPerson}', [SalesPersonController::class, 'show']);
        Route::put('{salesPerson}', [SalesPersonController::class, 'update']);
        Route::delete('{salesPerson}', [SalesPersonController::class, 'destroy']);
    });
        
 Route::middleware(['auth:api', 'locale'])->group(function () {
    Route::prefix('sales-invoices')
        ->middleware('permission:screen.sales_invoices')
        ->group(function () {
            Route::post('/', [SalesInvoiceController::class, 'store']);
            Route::put('/{salesInvoice}', [SalesInvoiceController::class, 'update']);
            Route::post('/{salesInvoice}/submit', [SalesInvoiceController::class, 'submit']);
            Route::get('/{salesInvoice}/print-preview', [SalesInvoiceController::class, 'printPreview']);
            Route::get('/{salesInvoice}/html', [SalesInvoiceController::class, 'showHtml']);
            Route::get('/{salesInvoice}/pdf', [SalesInvoiceController::class, 'downloadPdf']);
            Route::post('/{deliveryNote}/create-sales-invoice',[SalesInvoiceController::class, 'createFromDeliveryNote']
);
            Route::delete('/{salesInvoice}', [SalesInvoiceController::class, 'destroy']);
        });
        Route::middleware(['auth:api','permission:screen.sales_payments', 'locale'])->group(function () {
        Route::apiResource('sales-payments', SalesPaymentController::class)
    ->only(['index', 'store', 'show']);

Route::post('sales-payments/{salesPayment}/submit', [SalesPaymentController::class, 'submit']);
Route::post('sales-payments/{salesPayment}/cancel', [SalesPaymentController::class, 'cancel']);
        });

       Route::middleware(['auth:api', 'permission:screen.sales_person_performance_report', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/sales-person-performance', [SalesPersonPerformanceReportController::class, 'index']);
        Route::get('/sales-person-performance/pdf', [SalesPersonPerformanceReportController::class, 'pdf']);
    });
    Route::prefix('discount-approvals')
        ->middleware('role:CFO')
        ->group(function () {
            Route::get('/', [DiscountApprovalController::class, 'index']);
            Route::post('/{discountApprovalRequest}/respond', [DiscountApprovalController::class, 'respond']);
        });
});
    Route::middleware([ 'auth:api', 'permission:screen.discount_settings', 'locale'])
    ->prefix('discount-settings')->group(function () {
    Route::post('/', [DiscountSettingController::class, 'store']);
    Route::get('/', [DiscountSettingController::class, 'show']);
    Route::put('/{discountSetting}', [DiscountSettingController::class, 'update']);
    Route::delete('/{discountSetting}', [DiscountSettingController::class, 'destroy']);

});
Route::middleware(['auth:api', 'locale'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread', [NotificationController::class, 'unread']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});
Route::middleware(['auth:api','permission:screen.financial_reports','locale'])
->prefix('reports')->group(function () {
Route::get( '/customer-ledger/pdf', [CustomerLedgerReportController::class, 'pdf']);
});

Route::middleware(['auth:api','permission:screen.supplier_ledger_report','locale'])
->prefix('reports')->group(function () {
Route::get( '/supplier-ledger/pdf', [SupplierLedgerReportController::class, 'pdf']);
});

 Route::middleware([ 'auth:api', 'permission:screen.sales_returns', 'locale'])
    ->prefix('sales-returns')->group(function () {
Route::post('/', [SalesReturnController::class, 'store']);
Route::get('/{salesReturn}', [SalesReturnController::class, 'show']);
Route::put('/{salesReturn}', [SalesReturnController::class, 'update']);
Route::post('/{salesReturn}/submit', [SalesReturnController::class, 'submit']);
Route::post('/{salesReturn}/cancel', [SalesReturnController::class, 'cancel']);
Route::delete('/{salesReturn}', [SalesReturnController::class, 'destroy']);
});

Route::middleware(['auth:api', 'permission:screen.AccountsReceivableReport', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/accounts-receivable/pdf', [AccountsReceivableReportController::class, 'pdf']);
    });

    Route::middleware(['auth:api', 'permission:screen.SalesRegisterReport', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/sales-register/pdf', [SalesRegisterReportController::class, 'pdf']);
    });

    Route::middleware(['auth:api', 'permission:screen.ItemWiseSalesRegisterReport', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/item-wise-sales-register/pdf', [ItemWiseSalesRegisterReportController::class, 'pdf']);
    });
    Route::middleware(['auth:api', 'permission:screen.sales_payment_summary_report', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/sales-payment-summary/pdf', [SalesPaymentSummaryReportController::class, 'pdf']);
    });
    Route::middleware(['auth:api', 'permission:screen.purchase_reports', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/purchase-register/pdf', [PurchaseRegisterReportController::class, 'pdf']);
    });
    
    Route::middleware(['auth:api','permission:screen.purchase_payments', 'locale'])
    ->prefix('payment-entries')
    ->group(function () {
        Route::post('/from-purchase-invoice', [PaymentEntryController::class, 'storeFromPurchaseInvoice']);

        Route::put('/{paymentEntry}', [PaymentEntryController::class, 'update']);

        Route::post('/{paymentEntry}/submit', [PaymentEntryController::class, 'submit']);
        Route::post('/{paymentEntry}/cancel', [PaymentEntryController::class, 'cancel']);

        Route::get('/{paymentEntry}', [PaymentEntryController::class, 'show']);
        Route::get('/{paymentEntry}/print', [PaymentEntryController::class, 'print']);
        Route::delete('/{paymentEntry}', [PaymentEntryController::class, 'destroy']);
    });
  Route::middleware([ 'auth:api', 'permission:screen.purchase_returns', 'locale'])->prefix('purchase-returns')->group(function () {
    Route::get('/from-purchase-invoice/{purchaseInvoice}', [PurchaseReturnController::class, 'getDataFromPurchaseInvoice']);
    Route::post( '/',  [PurchaseReturnController::class, 'store']);
    Route::put('/{purchaseReturn}', [PurchaseReturnController::class, 'update'] );
    Route::get( '/{purchaseReturn}', [PurchaseReturnController::class, 'show']);
    Route::post('/{purchaseReturn}/submit', [PurchaseReturnController::class, 'submit']);
    Route::post('/{purchaseReturn}/cancel', [PurchaseReturnController::class, 'cancel'] );
    Route::delete( '/{purchaseReturn}',[PurchaseReturnController::class, 'destroy']); 
});
Route::get( '/reports/item-wise-purchase-register/pdf', [ItemWisePurchaseRegisterReportController::class, 'pdf'])->middleware([  'auth:api',  'permission:screen.item_wise_purchase_register_report', 'locale']);

Route::middleware(['auth:api', 'permission:screen.asset-capitalizations', 'locale'])
    ->prefix('asset-capitalizations')
    ->group(function () {
        Route::get('/lookups/target-assets', [AssetCapitalizationController::class, 'targetAssets']);
        Route::get('/lookups/consumed-assets', [AssetCapitalizationController::class, 'consumedAssets']);

        Route::get('/', [AssetCapitalizationController::class, 'index']);
        Route::post('/', [AssetCapitalizationController::class, 'store']);
        Route::get('/{assetCapitalization}', [AssetCapitalizationController::class, 'show']);
        Route::put('/{assetCapitalization}', [AssetCapitalizationController::class, 'update']);
        Route::post('/{assetCapitalization}/submit', [AssetCapitalizationController::class, 'submit']);
        Route::delete('/{assetCapitalization}', [AssetCapitalizationController::class, 'destroy']);
    });
    Route::middleware(['auth:api', 'permission:screen.asset_value_adjustments', 'locale'])
    ->prefix('asset-value-adjustments')
    ->group(function () {
        Route::get('/lookups/assets', [AssetValueAdjustmentController::class, 'availableAssets']);
        Route::get('/lookups/difference-accounts', [AssetValueAdjustmentController::class, 'differenceAccounts']);

        Route::get('/', [AssetValueAdjustmentController::class, 'index']);
        Route::post('/', [AssetValueAdjustmentController::class, 'store']);
        Route::get('/{assetValueAdjustment}', [AssetValueAdjustmentController::class, 'show']);
        Route::put('/{assetValueAdjustment}', [AssetValueAdjustmentController::class, 'update']);
        Route::post('/{assetValueAdjustment}/submit', [AssetValueAdjustmentController::class, 'submit']);
        Route::delete('/{assetValueAdjustment}', [AssetValueAdjustmentController::class, 'destroy']);
    });

    Route::middleware(['auth:api', 'permission:screen.asset_repair', 'locale'])
    ->prefix('asset-repairs')
    ->group(function () {
        Route::get('/lookups/assets', [AssetRepairController::class, 'availableAssets']);
        Route::get('/lookups/purchase-invoices', [AssetRepairController::class, 'purchaseInvoices']);

        Route::get('/', [AssetRepairController::class, 'index']);
        Route::post('/', [AssetRepairController::class, 'store']);
        Route::get('/{assetRepair}', [AssetRepairController::class, 'show']);
        Route::put('/{assetRepair}', [AssetRepairController::class, 'update']);
        Route::post('/{assetRepair}/submit', [AssetRepairController::class, 'submit']);
        Route::delete('/{assetRepair}', [AssetRepairController::class, 'destroy']);
    });

    Route::middleware(['auth:api', 'permission:screen.asset_Sale', 'locale'])
    ->prefix('asset-sales')
    ->group(function () {
        Route::get('/lookups/assets', [AssetSaleController::class, 'availableAssets']);
    Route::get('/lookups/warehouses', [AssetSaleController::class, 'warehouses']);
    Route::get('/lookups/accounts', [AssetSaleController::class, 'accounts']);

    Route::post('/create-invoice', [AssetSaleController::class, 'createInvoice']);

    Route::get('/', [AssetSaleController::class, 'index']);
    Route::post('/', [AssetSaleController::class, 'store']);

    Route::get('/{assetSale}', [AssetSaleController::class, 'show']);
    Route::put('/{assetSale}', [AssetSaleController::class, 'update']);
    Route::post('/{assetSale}/submit', [AssetSaleController::class, 'submit']);
    Route::delete('/{assetSale}', [AssetSaleController::class, 'destroy']);
    });
   
    Route::middleware(['auth:api', 'permission:screen.assets.scrappings', 'locale'])
    ->prefix('asset-scrappings')
    ->group(function () {
        Route::get('/lookups/assets', [AssetScrappingController::class, 'availableAssets']);

        Route::get('/', [AssetScrappingController::class, 'index']);
        Route::post('/', [AssetScrappingController::class, 'store']);
        Route::get('/{assetScrapping}', [AssetScrappingController::class, 'show']);
        Route::put('/{assetScrapping}', [AssetScrappingController::class, 'update']);
        Route::post('/{assetScrapping}/submit', [AssetScrappingController::class, 'submit']);
        Route::delete('/{assetScrapping}', [AssetScrappingController::class, 'destroy']);
    });

    Route::middleware(['auth:api', 'permission:screen.fixed.asset.register.report', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/fixed-asset-register/pdf', [FixedAssetRegisterReportController::class, 'pdf']);
    });

    Route::middleware(['auth:api', 'permission:screen.asset.depreciation.ledger.report', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/asset-depreciation-ledger/pdf', [AssetDepreciationLedgerReportController::class, 'pdf']);
    });
    Route::middleware(['auth:api', 'permission:screen.asset.depreciation.balance.report', 'locale'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/asset-depreciation-balance/pdf', [AssetDepreciationBalanceReportController::class, 'pdf']);
    });

    Route::middleware(['auth:api', 'permission:screen.account_closing', 'locale'])
    ->prefix('financial-years')
    ->group(function () {
        Route::get('/', [FinancialYearController::class, 'index']);
        Route::post('/', [FinancialYearController::class, 'store']);
        Route::get('/{financialYear}', [FinancialYearController::class, 'show']);
        Route::put('/{financialYear}', [FinancialYearController::class, 'update']);
        Route::post('/{financialYear}/close', [FinancialYearController::class, 'close']);
        Route::post('/{financialYear}/reopen', [FinancialYearController::class, 'reopen']);
        Route::delete('/{financialYear}', [FinancialYearController::class, 'destroy']);
    });
    Route::middleware(['auth:api','permission:screen.tax_declaration_settings'])->group(function () {
    Route::get('/tax-declaration-setting', [TaxDeclarationSettingController::class, 'show']);
    Route::post('/tax-declaration-setting', [TaxDeclarationSettingController::class, 'store']);
    Route::put('/tax-declaration-setting', [TaxDeclarationSettingController::class, 'update']);
});

Route::middleware(['auth:api','permission:screen.tax_declaration_reports'])->group(function () {
    Route::get('/tax-declaration-report/pdf', [TaxDeclarationReportController::class, 'pdf']);
});