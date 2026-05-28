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

    Route::get('/reports/daily', [
        ReportController::class,
        'daily'
    ])->name('reports.daily');

    Route::get('/reports/monthly', [
        ReportController::class,
        'monthly'
    ])->name('reports.monthly');

    Route::get('/reports/yearly', [
        ReportController::class,
        'yearly'
    ])->name('reports.yearly');


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
    | COMPANY INCOMES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'company-incomes',
        IncomeController::class
    );

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

    /*
    |--------------------------------------------------------------------------
    | STENDI DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/stendi', [
        ToiletAttendantController::class,
        'dashboard'
    ])->name('stendi.dashboard');

    /*
    |--------------------------------------------------------------------------
    | SOKONI DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/sokoni', [
        ToiletAttendantController::class,
        'dashboard'
    ])->name('sokoni.dashboard');

    /*
    |--------------------------------------------------------------------------
    | STENDI ADD ENTRY
    |--------------------------------------------------------------------------
    */

    Route::get('/stendi/add-entry', [
        ToiletAttendantController::class,
        'createEntry'
    ])->name('stendi.entry.create');

    Route::post('/stendi/add-entry', [
        ToiletAttendantController::class,
        'storeEntry'
    ])->name('stendi.entry.store');

    /*
    |--------------------------------------------------------------------------
    | SOKONI ADD ENTRY
    |--------------------------------------------------------------------------
    */

    Route::get('/sokoni/add-entry', [
        ToiletAttendantController::class,
        'createEntry'
    ])->name('sokoni.entry.create');

    Route::post('/sokoni/add-entry', [
        ToiletAttendantController::class,
        'storeEntry'
    ])->name('sokoni.entry.store');

    /*
    |--------------------------------------------------------------------------
    | STENDI EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::get('/stendi/expenses', [
        ToiletAttendantController::class,
        'expenses'
    ])->name('stendi.expenses');

    /*
    |--------------------------------------------------------------------------
    | SOKONI EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::get('/sokoni/expenses', [
        ToiletAttendantController::class,
        'expenses'
    ])->name('sokoni.expenses');

    /*
    |--------------------------------------------------------------------------
    | STORE TOILET EXPENSE
    |--------------------------------------------------------------------------
    */

    Route::post('/expense/store/{entry_date?}', [
        ToiletAttendantController::class,
        'storeExpense'
    ])->name('expense.store');

    /*
    |--------------------------------------------------------------------------
    | UPDATE TOILET EXPENSE
    |--------------------------------------------------------------------------
    */

    Route::put('/expense/update/{id}', [
        ToiletAttendantController::class,
        'updateExpense'
    ])->name('expense.update');

    /*
    |--------------------------------------------------------------------------
    | DELETE TOILET EXPENSE
    |--------------------------------------------------------------------------
    */

    Route::delete('/expense/delete/{id}', [
        ToiletAttendantController::class,
        'deleteExpense'
    ])->name('expense.delete');

    /*
    |--------------------------------------------------------------------------
    | UPDATE DAILY ENTRY
    |--------------------------------------------------------------------------
    */

    Route::put('/entry/update/{id}', [
        ToiletAttendantController::class,
        'updateEntry'
    ])->name('entry.update');

    /*
    |--------------------------------------------------------------------------
    | STENDI REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get('/stendi/reports', [
        ToiletAttendantController::class,
        'reports'
    ])->name('stendi.reports');

    /*
    |--------------------------------------------------------------------------
    | SOKONI REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get('/sokoni/reports', [
        ToiletAttendantController::class,
        'reports'
    ])->name('sokoni.reports');

    /*
    |--------------------------------------------------------------------------
    | UPDATE DAILY ENTRY BALANCES
    |--------------------------------------------------------------------------
    */

    Route::put('/daily-entry/update/{id}', [
        ToiletDailyEntryController::class,
        'update'
    ])->name('daily-entry.update');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';