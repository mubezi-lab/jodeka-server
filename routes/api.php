<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\StockApiController;
use App\Http\Controllers\Api\LivestockApiController;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\SavingApiController;

/*
|--------------------------------------------------------------------------
| TEST
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {

    return response()->json([
        'message' => 'API Working'
    ]);

});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthApiController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthApiController::class, 'user']);

    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    Route::get('/products', [ProductApiController::class, 'index']);

    Route::get('/stocks', [StockApiController::class, 'index']);

    Route::get('/livestocks', [LivestockApiController::class, 'index']);

    Route::get('/loans', [LoanApiController::class, 'index']);

    Route::get('/savings', [SavingApiController::class, 'index']);

});