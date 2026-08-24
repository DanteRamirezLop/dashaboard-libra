<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\LoanController as ApiLoanController;
use App\Http\Controllers\PingController;
use App\Http\Controllers\SalesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('api.token')->get('/ping', [PingController::class,'index']);
Route::middleware('api.token')->get('/sales', [SalesController::class,'index']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->prefix('dashboard')->group(function () {
    Route::get('loans', [ApiLoanController::class, 'index']);
});

