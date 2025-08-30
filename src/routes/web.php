<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TradeController;

Route::get('/', [StockController::class, 'index']);
Route::get('/stock/{code}', [StockController::class, 'show']);
Route::post('/stock', [StockController::class, 'store']);
Route::put('/stock/{code}', [StockController::class, 'update']);
Route::delete('/stock/{code}', [StockController::class, 'destroy']);
Route::get('/trade', [TradeController::class, 'index']);
Route::get('/trade/{code}', [TradeController::class, 'show']);
Route::post('/trade', [TradeController::class, 'store']);
Route::put('/trade/{code}', [TradeController::class, 'update']);
Route::delete('/trade/{code}', [TradeController::class, 'destroy']);