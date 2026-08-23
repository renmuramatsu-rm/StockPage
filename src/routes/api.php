<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SbiHoldingController;
use App\Http\Controllers\Api\StockPageController;
use App\Http\Controllers\Api\ThemeController;

Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 銘柄マスタ・財務データは市場データとして誰でも閲覧可能
Route::get('/stocks', [StockPageController::class, 'index']);
Route::get('/stocks/{stock}', [StockPageController::class, 'show']);

// テーマ・SBI保有株はユーザーごとに分離した個人データのためログイン必須
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [ThemeController::class, 'dashboard']);
    Route::post('/themes', [ThemeController::class, 'store']);
    Route::put('/themes/{theme}', [ThemeController::class, 'update']);
    Route::delete('/themes/{theme}', [ThemeController::class, 'destroy']);
    Route::get('/themes/{theme}', [ThemeController::class, 'show']);

    Route::get('/stocks/{stock}/themes', [StockPageController::class, 'editThemes']);
    Route::put('/stocks/{stock}/themes', [StockPageController::class, 'updateThemes']);

    Route::get('/sbi-holdings', [SbiHoldingController::class, 'index']);
    Route::get('/sbi-holdings/stocks', [SbiHoldingController::class, 'stocks']);
    Route::post('/sbi-holdings', [SbiHoldingController::class, 'store']);
    Route::get('/sbi-holdings/{sbi_holding}', [SbiHoldingController::class, 'show']);
    Route::put('/sbi-holdings/{sbi_holding}', [SbiHoldingController::class, 'update']);
    Route::delete('/sbi-holdings/{sbi_holding}', [SbiHoldingController::class, 'destroy']);
});
