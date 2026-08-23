<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TradeController;
use App\Http\Controllers\GoogleAuthController;

// Google OAuth is the only server-rendered auth step left — everything
// else (login/register/logout, themes, stocks, SBI holdings) is served
// as JSON from routes/api.php and rendered by the Next.js frontend.
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::get('/stock/{code}', [StockController::class, 'show']);
Route::post('/stock', [StockController::class, 'store']);
Route::put('/stock/{code}', [StockController::class, 'update']);
Route::delete('/stock/{code}', [StockController::class, 'destroy']);
Route::get('/trade', [TradeController::class, 'index']);
Route::get('/trade/{code}', [TradeController::class, 'show']);
Route::post('/trade', [TradeController::class, 'store']);
Route::put('/trade/{code}', [TradeController::class, 'update']);
Route::delete('/trade/{code}', [TradeController::class, 'destroy']);
