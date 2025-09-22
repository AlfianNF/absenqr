<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\DashboardController;

Route::get('/',[AuthController::class,'loginPage'])->name('loginPage');
Route::post('/',[AuthController::class,'login'])->name('login');

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/user', [DashboardController::class, 'user'])->name('dashboard.user');
    Route::get('/setting', [DashboardController::class, 'setting'])->name('dashboard.setting');
    Route::get('/profil', [DashboardController::class, 'profil'])->name('dashboard.profil');
});

Route::get('/qr-code', [QRCodeController::class, 'index']);
Route::get('/qr-code/{id}', [QRCodeController::class, 'show']);
Route::post('/qr-code', [QRCodeController::class, 'generate'])->name('qr-code.generate');