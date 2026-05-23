<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\LivestockLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Role Test Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->get('/admin', fn () => "Admin Only");

Route::middleware(['auth', 'role:manager'])
    ->get('/manager', fn () => "Manager Only");

Route::middleware(['auth', 'role:employee'])
    ->get('/employee', fn () => "Employee Only");

/*
|--------------------------------------------------------------------------
| Admin Routes (Main System)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Core Modules
    |--------------------------------------------------------------------------
    */
    Route::resource('businesses', BusinessController::class);
    Route::resource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | Stock Module
    |--------------------------------------------------------------------------
    */
    Route::resource('stocks', StockController::class);

    /*
    |--------------------------------------------------------------------------
    | Purchase Module
    |--------------------------------------------------------------------------
    */
    Route::resource('purchases', PurchaseController::class);

    /*
    |--------------------------------------------------------------------------
    | 🐔 Livestock Module
    |--------------------------------------------------------------------------
    */

    // Livestock CRUD
    Route::resource('livestocks', LivestockController::class);

    /*
    |--------------------------------------------------------------------------
    | 🔥 Livestock Logs (EXPENSE + MORTALITY)
    |--------------------------------------------------------------------------
    */

    // CREATE PAGE
    Route::get('/livestock-logs/create', [LivestockLogController::class, 'create'])
        ->name('livestock-logs.create');

    // STORE
    Route::post('/livestock-logs', [LivestockLogController::class, 'store'])
        ->name('livestock-logs.store');

    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */
    Route::get('/stock-data', [StockController::class, 'getStockData'])
        ->name('stocks.data');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])
        ->name('reports.monthly');

    /*
    |--------------------------------------------------------------------------
    | Users & Expenses
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);
    Route::resource('expenses', ExpenseController::class);

    /*
    |--------------------------------------------------------------------------
    | 🗄️ Database Tables Explorer
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

        return view('database.tables', compact('result'));

    })->name('database.tables');

});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';