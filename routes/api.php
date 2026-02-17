<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// =============================================
// HERKESE AÇIK (Token gerekmez)
// =============================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// =============================================
// GİRİŞ YAPMIŞ KULLANICI (Token gerekli)
// =============================================
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Buraya ileride diğer route'lar eklenecek:
    // Route::prefix('profile')->group(...)
    // Route::prefix('discover')->group(...)
    // Route::prefix('chat')->group(...)
});