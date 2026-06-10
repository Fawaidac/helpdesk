<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataKomplainController;
use App\Http\Controllers\Api\RevenueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/check-pin', [AuthController::class, 'checkPin']);
Route::get('/pde', [DataKomplainController::class, 'getDataPde']);

Route::prefix('komplain')->group(function () {
    Route::get('/', [DataKomplainController::class, 'index']);
    Route::get('/dashboard', [DataKomplainController::class, 'dashboard']);
});

Route::prefix('revenue')->group(function () {
    Route::get('/', [RevenueController::class, 'index']);
    Route::post('/', [RevenueController::class, 'store']);
    Route::get('/years', [RevenueController::class, 'years']);
    Route::get('/detail', [RevenueController::class, 'showByYear']);
});