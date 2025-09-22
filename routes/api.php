<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\SettingPresensiController;

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);

    Route::resource('setting-presensi', SettingPresensiController::class);
    Route::resource('presensi', PresensiController::class);
    Route::get('history-presensi',[PresensiController::class,'history']);

    Route::get('logout', [AuthController::class, 'logout']);
});


Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('users/{id}',[AuthController::class,'showUser']);