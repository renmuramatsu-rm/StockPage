<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TradeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\SbiHoldingController;
use App\Http\Controllers\StockPageController;
use App\Http\Controllers\ThemeController;

Route::get('/login', [AuthController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google')->middleware('guest');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('guest');

// 閲覧は誰でも可能
Route::get('/', [ThemeController::class, 'index'])->name('themes.dashboard');
Route::get('/themes/{theme}', [ThemeController::class, 'show'])->name('themes.show');

Route::get('/stocks', [StockPageController::class, 'index'])->name('stocks.index');
Route::get('/stocks/{stock}', [StockPageController::class, 'show'])->name('stocks.show');

// 自分のデータを変更する操作はログインが必要
Route::middleware('auth')->group(function () {
    Route::get('/themes/create', [ThemeController::class, 'create'])->name('themes.create');
    Route::post('/themes', [ThemeController::class, 'store'])->name('themes.store');
    Route::get('/themes/{theme}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
    Route::put('/themes/{theme}', [ThemeController::class, 'update'])->name('themes.update');
    Route::delete('/themes/{theme}', [ThemeController::class, 'destroy'])->name('themes.destroy');

    Route::get('/stocks/{stock}/themes', [StockPageController::class, 'editThemes'])->name('stocks.themes.edit');
    Route::put('/stocks/{stock}/themes', [StockPageController::class, 'updateThemes'])->name('stocks.themes.update');

    Route::resource('sbi-holdings', SbiHoldingController::class);
});

Route::get('/stock/{code}', [StockController::class, 'show']);
Route::post('/stock', [StockController::class, 'store']);
Route::put('/stock/{code}', [StockController::class, 'update']);
Route::delete('/stock/{code}', [StockController::class, 'destroy']);
Route::get('/trade', [TradeController::class, 'index']);
Route::get('/trade/{code}', [TradeController::class, 'show']);
Route::post('/trade', [TradeController::class, 'store']);
Route::put('/trade/{code}', [TradeController::class, 'update']);
Route::delete('/trade/{code}', [TradeController::class, 'destroy']);