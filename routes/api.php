<?php

use App\Http\Controllers\Api\DataKomplainController;
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

Route::get('/komplain', [DataKomplainController::class, 'index']);
Route::get('/komplain/dashboard', [DataKomplainController::class, 'dashboard']);
