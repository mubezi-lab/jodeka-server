<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\StockApiController;
use App\Http\Controllers\Api\LivestockApiController;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\SavingApiController;
use App\Http\Controllers\Api\BagambakamoSmsController;

use App\Http\Controllers\HotspotPaymentSmsController;
use App\Http\Controllers\HotspotPaymentVerificationController;
use App\Http\Controllers\PalmPesaHotspotPaymentController;
use App\Http\Controllers\PalmPesaCallbackController;

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
| HOTSPOT PAYMENT SMS
|--------------------------------------------------------------------------
*/

Route::post('/hotspot/payments/sms', [
    HotspotPaymentSmsController::class,
    'store'
]);

/*
|--------------------------------------------------------------------------
| HOTSPOT PAYMENT - PALM PESA
|--------------------------------------------------------------------------
|
| Starts a Palm Pesa mobile-money payment request for a hotspot package.
|
| This route does NOT generate a voucher immediately.
| Voucher generation only continues after Palm Pesa confirms
| that the transaction status is COMPLETED.
|
| Final URL:
| /api/hotspot/payments/palmpesa
|
*/

Route::post('/hotspot/payments/palmpesa', [
    PalmPesaHotspotPaymentController::class,
    'store'
])->name('hotspot.palmpesa.payment');

/*
|--------------------------------------------------------------------------
| PALM PESA CALLBACK
|--------------------------------------------------------------------------
|
| Palm Pesa sends final payment results to this endpoint.
|
| JODEKA verifies the transaction with Palm Pesa before treating
| the payment as completed and allowing voucher generation.
|
| Final URL:
| https://jodeka.co.tz/api/palmpesa/callback
|
*/

Route::post(
    '/palmpesa/callback',
    PalmPesaCallbackController::class
)->name('palmpesa.callback');

/*
|--------------------------------------------------------------------------
| BAGAMBAKAMO M-KOBA SMS
|--------------------------------------------------------------------------
|
| Receives BAGAMBA KAMO SMS messages forwarded from the SMS Forwarder app.
| Laravel automatically adds the /api prefix to this route.
|
| Final URL:
| /api/bagambakamo/sms
|
*/

Route::post('/bagambakamo/sms', [
    BagambakamoSmsController::class,
    'store'
]);

/*
|--------------------------------------------------------------------------
| HOTSPOT PAYMENT VERIFICATION
|--------------------------------------------------------------------------
|
| Public endpoint used by the Jodeka WiFi captive portal.
| Customer enters the payment phone/reference information after paying.
|
*/

Route::post('/hotspot/payments/verify', [
    HotspotPaymentVerificationController::class,
    'verify'
]);

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [
    AuthApiController::class,
    'login'
]);

/*
|--------------------------------------------------------------------------
| PROTECTED API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [
        AuthApiController::class,
        'user'
    ]);

    Route::post('/logout', [
        AuthApiController::class,
        'logout'
    ]);

    Route::get('/dashboard', [
        DashboardApiController::class,
        'index'
    ]);

    Route::get('/products', [
        ProductApiController::class,
        'index'
    ]);

    Route::get('/stocks', [
        StockApiController::class,
        'index'
    ]);

    Route::get('/livestocks', [
        LivestockApiController::class,
        'index'
    ]);

    Route::get('/loans', [
        LoanApiController::class,
        'index'
    ]);

    Route::get('/savings', [
        SavingApiController::class,
        'index'
    ]);

});