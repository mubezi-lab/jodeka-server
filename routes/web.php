<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PurchaseController;

use App\Http\Controllers\LivestockController;
use App\Http\Controllers\LivestockLogController;

use App\Http\Controllers\UserController;

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;

use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanPaymentController;

use App\Http\Controllers\SavingController;

use App\Http\Controllers\ReportController;

use App\Http\Controllers\ToiletController;
use App\Http\Controllers\ToiletAttendantController;
use App\Http\Controllers\ToiletDailyEntryController;

use App\Http\Controllers\FixedExpenseController;
use App\Http\Controllers\FixedExpensePaymentController;

use App\Http\Controllers\NetworkRouterController;
use App\Http\Controllers\HotspotProfileController;
use App\Http\Controllers\HotspotVoucherController;
use App\Http\Controllers\HotspotCustomerInvitationController;
use App\Http\Controllers\HotspotPermanentUserController;
use App\Http\Controllers\HotspotCustomerController;
use App\Http\Controllers\WifiPortalController;

use App\Http\Controllers\DailyCashEntryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DebtPaymentController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialAccountTransferController;
use App\Http\Controllers\ProcurementController;


/*
|--------------------------------------------------------------------------
| BAGAMBAKAMO CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Bagambakamo\AdminController as BagambakamoAdminController;
use App\Http\Controllers\Bagambakamo\MemberController as BagambakamoMemberController;
use App\Http\Controllers\Bagambakamo\PaymentController as BagambakamoPaymentController;
use App\Http\Controllers\Bagambakamo\EventController as BagambakamoEventController;
use App\Http\Controllers\Bagambakamo\ReportController as BagambakamoReportController;
use App\Http\Controllers\Bagambakamo\SmsController as BagambakamoSmsController;
use App\Http\Controllers\Bagambakamo\SmsReportController as BagambakamoSmsReportController;
use App\Http\Controllers\Bagambakamo\PendingTransactionController as BagambakamoPendingTransactionController;


/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return view('auth.login');

})->name('home');


/*
|--------------------------------------------------------------------------
| PUBLIC WI-FI CAPTIVE PORTAL
|--------------------------------------------------------------------------
|
| This route must remain PUBLIC.
| Hotspot customers are not Laravel users and must not be required
| to authenticate with the JODEKA admin system.
|
*/

Route::get('/wifi', [
    WifiPortalController::class,
    'index'
])->name('wifi.portal');


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'role:admin'
])->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');
});


/*
|--------------------------------------------------------------------------
| CUSTOMERS AND GENERAL DEBTS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,manager,employee'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/create', [DebtController::class, 'create'])->name('debts.create');
    Route::post('/debts', [DebtController::class, 'store'])->name('debts.store');
    Route::get('/debts/{debt}', [DebtController::class, 'show'])->name('debts.show');
    Route::post('/debts/{debt}/payments', [DebtPaymentController::class, 'store'])
        ->name('debts.payments.store');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/financial-accounts', [FinancialAccountController::class, 'index'])->name('financial-accounts.index');
    Route::post('/financial-accounts', [FinancialAccountController::class, 'store'])->name('financial-accounts.store');
    Route::put('/financial-accounts/{financialAccount}', [FinancialAccountController::class, 'update'])->name('financial-accounts.update');
    Route::patch('/financial-accounts/{financialAccount}/toggle', [FinancialAccountController::class, 'toggle'])->name('financial-accounts.toggle');
});

Route::middleware(['auth', 'role:admin,manager,employee'])->group(function () {
    Route::get('/financial-account-transfers', [FinancialAccountTransferController::class, 'index'])->name('financial-account-transfers.index');
    Route::post('/financial-account-transfers', [FinancialAccountTransferController::class, 'store'])->name('financial-account-transfers.store');
});

Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::post('/financial-account-transfers/{transfer}/confirm', [FinancialAccountTransferController::class, 'confirm'])->name('financial-account-transfers.confirm');
    Route::post('/financial-account-transfers/{transfer}/reject', [FinancialAccountTransferController::class, 'reject'])->name('financial-account-transfers.reject');
});

Route::middleware(['auth', 'role:admin,manager,employee'])->group(function () {
    Route::get('/procurement', [ProcurementController::class, 'index'])->name('procurement.index');
    Route::post('/procurement/requests', [ProcurementController::class, 'storeRequest'])->name('procurement.requests.store');
    Route::get('/procurement/requests/{stockRequest}', [ProcurementController::class, 'show'])->name('procurement.requests.show');
    Route::post('/procurement/orders/{purchaseOrder}/receive', [ProcurementController::class, 'receive'])->name('procurement.orders.receive');
});

Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::post('/procurement/requests/{stockRequest}/review', [ProcurementController::class, 'review'])->name('procurement.requests.review');
    Route::post('/procurement/requests/{stockRequest}/order', [ProcurementController::class, 'order'])->name('procurement.requests.order');
});


/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy'
    ])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| ROLE TEST ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->get('/admin', fn () => 'Admin Only');

Route::middleware(['auth', 'role:manager'])
    ->get('/manager', fn () => 'Manager Only');

Route::middleware(['auth', 'role:employee'])
    ->get('/employee', fn () => 'Employee Only');


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BUSINESSES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'businesses',
        BusinessController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'products',
        ProductController::class
    );


    /*
    |--------------------------------------------------------------------------
    | STOCKS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'stocks',
        StockController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PURCHASES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'purchases',
        PurchaseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | LIVESTOCKS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'livestocks',
        LivestockController::class
    );


    /*
    |--------------------------------------------------------------------------
    | LIVESTOCK LOGS
    |--------------------------------------------------------------------------
    */

    Route::get('/livestock-logs/create', [
        LivestockLogController::class,
        'create'
    ])->name('livestock-logs.create');

    Route::post('/livestock-logs', [
        LivestockLogController::class,
        'store'
    ])->name('livestock-logs.store');


    /*
    |--------------------------------------------------------------------------
    | STOCK AJAX DATA
    |--------------------------------------------------------------------------
    */

    Route::get('/stock-data', [
        StockController::class,
        'getStockData'
    ])->name('stocks.data');


    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get('/reports', [
        ReportController::class,
        'index'
    ])->name('reports.index');

    Route::get('/reports/monthly', [
        ReportController::class,
        'monthly'
    ])->name('reports.monthly');


    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'users',
        UserController::class
    );


    /*
    |--------------------------------------------------------------------------
    | COMPANY EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'company-expenses',
        ExpenseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | FIXED EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'fixed-expenses',
        FixedExpenseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | FIXED EXPENSE PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::get('/fixed-expense-payments', [
        FixedExpensePaymentController::class,
        'index'
    ])->name('fixed-expense-payments.index');

    Route::post('/fixed-expense-payments/{fixedExpense}/pay', [
        FixedExpensePaymentController::class,
        'pay'
    ])->name('fixed-expense-payments.pay');


    /*
    |--------------------------------------------------------------------------
    | COMPANY INCOMES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'company-incomes',
        IncomeController::class
    );


    /*
    |--------------------------------------------------------------------------
    | COMPANY NETWORK
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'network-routers',
        NetworkRouterController::class
    );

    Route::post(
        '/network-routers/{networkRouter}/test-connection',
        [NetworkRouterController::class, 'testConnection']
    )->name('network-routers.test');

    Route::post(
        '/network-routers/{networkRouter}/sync-profiles',
        [NetworkRouterController::class, 'syncProfiles']
    )->name('network-routers.sync-profiles');


    /*
    |--------------------------------------------------------------------------
    | HOTSPOT PROFILES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hotspot-profiles',
        [HotspotProfileController::class, 'index']
    )->name('hotspot-profiles.index');

    Route::get(
        '/hotspot-profiles/{hotspotProfile}/edit',
        [HotspotProfileController::class, 'edit']
    )->name('hotspot-profiles.edit');

    Route::put(
        '/hotspot-profiles/{hotspotProfile}',
        [HotspotProfileController::class, 'update']
    )->name('hotspot-profiles.update');


    /*
    |--------------------------------------------------------------------------
    | HOTSPOT VOUCHERS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hotspot-vouchers',
        [HotspotVoucherController::class, 'index']
    )->name('hotspot-vouchers.index');

    Route::get(
        '/hotspot-vouchers/create',
        [HotspotVoucherController::class, 'create']
    )->name('hotspot-vouchers.create');

    Route::get(
        '/hotspot-vouchers/{hotspotVoucher}',
        [HotspotVoucherController::class, 'show']
    )->name('hotspot-vouchers.show');

    Route::get(
        '/hotspot-vouchers-mikrotik',
        [HotspotVoucherController::class, 'mikrotik']
    )->name('hotspot-vouchers.mikrotik');

    Route::post(
        '/hotspot-vouchers',
        [HotspotVoucherController::class, 'store']
    )->name('hotspot-vouchers.store');

    Route::post(
        '/hotspot-vouchers/sync-status',
        [HotspotVoucherController::class, 'syncStatus']
    )->name('hotspot-vouchers.sync-status');

    Route::post(
        '/hotspot-vouchers/customer-invitations',
        [HotspotCustomerInvitationController::class, 'store']
    )->name('hotspot-vouchers.customer-invitations.store');

    Route::get(
        '/hotspot-customers',
        [HotspotCustomerController::class, 'index']
    )->name('hotspot-customers.index');

    Route::post(
        '/hotspot-customers/broadcasts',
        [HotspotCustomerController::class, 'broadcast']
    )->name('hotspot-customers.broadcasts.store');

    Route::post(
        '/hotspot-vouchers/{hotspotVoucher}/cancel',
        [HotspotVoucherController::class, 'cancel']
    )->name('hotspot-vouchers.cancel');

    /*
    |--------------------------------------------------------------------------
    | PERMANENT HOTSPOT USERS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hotspot-permanent-users',
        [HotspotPermanentUserController::class, 'index']
    )->name('hotspot-permanent-users.index');

    Route::post(
        '/hotspot-permanent-users',
        [HotspotPermanentUserController::class, 'store']
    )->name('hotspot-permanent-users.store');

    Route::patch(
        '/hotspot-permanent-users/{hotspotPermanentUser}/toggle',
        [HotspotPermanentUserController::class, 'toggle']
    )->name('hotspot-permanent-users.toggle');

    Route::post(
        '/hotspot-permanent-users/{hotspotPermanentUser}/payments',
        [HotspotPermanentUserController::class, 'payment']
    )->name('hotspot-permanent-users.payments.store');

    Route::post(
        '/hotspot-permanent-users/sync',
        [HotspotPermanentUserController::class, 'sync']
    )->name('hotspot-permanent-users.sync');


    /*
    |--------------------------------------------------------------------------
    | BAGAMBAKAMO
    |--------------------------------------------------------------------------
    */

    Route::prefix('bagambakamo')
        ->name('bagambakamo.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO DASHBOARD
            |--------------------------------------------------------------------------
            */

            Route::get('/', [
                BagambakamoAdminController::class,
                'index'
            ])->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO MEMBERS
            |--------------------------------------------------------------------------
            */

            Route::get('/members', [
                BagambakamoMemberController::class,
                'index'
            ])->name('members.index');

            Route::post('/members', [
                BagambakamoMemberController::class,
                'store'
            ])->name('members.store');

            Route::get('/members/{member}', [
                BagambakamoMemberController::class,
                'show'
            ])->name('members.show');

            Route::delete('/members/{member}', [
                BagambakamoMemberController::class,
                'destroy'
            ])->name('members.destroy');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO PAYMENTS
            |--------------------------------------------------------------------------
            */

            Route::post('/payments', [
                BagambakamoPaymentController::class,
                'store'
            ])->name('payments.store');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO EVENTS
            |--------------------------------------------------------------------------
            */

            Route::post('/events', [
                BagambakamoEventController::class,
                'store'
            ])->name('events.store');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO PENDING M-KOBA TRANSACTIONS
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/pending-transactions/{pendingTransaction}/event',
                [
                    BagambakamoPendingTransactionController::class,
                    'storeEvent'
                ]
            )->name('pending-transactions.event');


            Route::post(
                '/pending-transactions/{pendingTransaction}/expense',
                [
                    BagambakamoPendingTransactionController::class,
                    'storeExpense'
                ]
            )->name('pending-transactions.expense');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO REPORTS
            |--------------------------------------------------------------------------
            */

            Route::get('/reports', [
                BagambakamoReportController::class,
                'index'
            ])->name('reports.index');

            Route::get('/report/pdf', [
                BagambakamoReportController::class,
                'downloadPDF'
            ])->name('report.pdf');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO SMS REPORTS
            |--------------------------------------------------------------------------
            */

            Route::get('/sms-reports', [
                BagambakamoSmsReportController::class,
                'index'
            ])->name('sms.reports');


            /*
            |--------------------------------------------------------------------------
            | BAGAMBAKAMO SMS DEBTORS
            |--------------------------------------------------------------------------
            */

            Route::post('/sms/debtors', [
                BagambakamoSmsController::class,
                'sendToDebtors'
            ])->name('sms.debtors');
        });


    /*
    |--------------------------------------------------------------------------
    | SAVINGS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'savings',
        SavingController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SAVING DEPOSITS
    |--------------------------------------------------------------------------
    */

    Route::get('/savings/{saving}/deposit', [
        SavingController::class,
        'depositForm'
    ])->name('savings.deposit.form');

    Route::post('/savings/{saving}/deposit', [
        SavingController::class,
        'depositStore'
    ])->name('savings.deposit.store');


    /*
    |--------------------------------------------------------------------------
    | SAVING WITHDRAWALS
    |--------------------------------------------------------------------------
    */

    Route::get('/savings/{saving}/withdraw', [
        SavingController::class,
        'withdrawForm'
    ])->name('savings.withdraw.form');

    Route::post('/savings/{saving}/withdraw', [
        SavingController::class,
        'withdrawStore'
    ])->name('savings.withdraw.store');


    /*
    |--------------------------------------------------------------------------
    | LOANS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'loans',
        LoanController::class
    );


    /*
    |--------------------------------------------------------------------------
    | LOAN PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::post('/loans/{loan}/payments', [
        LoanPaymentController::class,
        'store'
    ])->name('loan-payments.store');

    Route::delete('/loan-payments/{payment}', [
        LoanPaymentController::class,
        'destroy'
    ])->name('loan-payments.destroy');


    /*
    |--------------------------------------------------------------------------
    | TOILETS
    |--------------------------------------------------------------------------
    */

    Route::get('/toilets', [
        ToiletController::class,
        'index'
    ])->name('toilets.index');


    /*
    |--------------------------------------------------------------------------
    | DATABASE TABLES VIEWER
    |--------------------------------------------------------------------------
    */

    Route::get('/database/tables', function () {

        $database = DB::getDatabaseName();

        $tables = DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '{$database}'
            ORDER BY TABLE_NAME ASC
        ");

        $result = [];

        foreach ($tables as $table) {

            $tableName = $table->TABLE_NAME;

            $columns = DB::select("
                SELECT
                    COLUMN_NAME,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_KEY,
                    COLUMN_DEFAULT,
                    EXTRA
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '{$database}'
                AND TABLE_NAME = '{$tableName}'
            ");

            $result[$tableName] = $columns;
        }

        return view(
            'database.tables',
            compact('result')
        );

    })->name('database.tables');
});


/*
|--------------------------------------------------------------------------
| TOILET EMPLOYEE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:employee'
])->group(function () {

    Route::get('/stendi', [
        ToiletAttendantController::class,
        'dashboard'
    ])->name('stendi.dashboard');

    Route::get('/sokoni', [
        ToiletAttendantController::class,
        'dashboard'
    ])->name('sokoni.dashboard');

    Route::get('/stendi/add-entry', [
        ToiletAttendantController::class,
        'createEntry'
    ])->name('stendi.entry.create');

    Route::post('/stendi/add-entry', [
        ToiletAttendantController::class,
        'storeEntry'
    ])->name('stendi.entry.store');

    Route::get('/sokoni/add-entry', [
        ToiletAttendantController::class,
        'createEntry'
    ])->name('sokoni.entry.create');

    Route::post('/sokoni/add-entry', [
        ToiletAttendantController::class,
        'storeEntry'
    ])->name('sokoni.entry.store');

    Route::get('/stendi/expenses', [
        ToiletAttendantController::class,
        'expenses'
    ])->name('stendi.expenses');

    Route::get('/sokoni/expenses', [
        ToiletAttendantController::class,
        'expenses'
    ])->name('sokoni.expenses');

    Route::post('/expense/store/{entry_date?}', [
        ToiletAttendantController::class,
        'storeExpense'
    ])->name('expense.store');

    Route::put('/expense/update/{id}', [
        ToiletAttendantController::class,
        'updateExpense'
    ])->name('expense.update');

    Route::delete('/expense/delete/{id}', [
        ToiletAttendantController::class,
        'deleteExpense'
    ])->name('expense.delete');

    Route::put('/entry/update/{id}', [
        ToiletAttendantController::class,
        'updateEntry'
    ])->name('entry.update');

    Route::get('/stendi/reports', [
        ToiletAttendantController::class,
        'reports'
    ])->name('stendi.reports');

    Route::get('/sokoni/reports', [
        ToiletAttendantController::class,
        'reports'
    ])->name('sokoni.reports');

    Route::put('/daily-entry/update/{id}', [
        ToiletDailyEntryController::class,
        'update'
    ])->name('daily-entry.update');
});


/*
|--------------------------------------------------------------------------
| DAILY CASH (ADMIN & MANAGER)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,manager'
])->group(function () {

    Route::get('/daily-cash', [
        DailyCashEntryController::class,
        'index'
    ])->name('daily-cash.index');

    Route::get('/daily-cash/create', [
        DailyCashEntryController::class,
        'create'
    ])->name('daily-cash.create');

    Route::post('/daily-cash', [
        DailyCashEntryController::class,
        'store'
    ])->name('daily-cash.store');
});


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
