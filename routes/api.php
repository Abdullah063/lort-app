<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\GoalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\InterestController;

// =============================================
// HERKESE AÇIK 
// =============================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// =============================================
// GİRİŞ YAPMIŞ KULLANICI 
// =============================================
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('profile')->group(function () {
        Route::post('/', [ProfileController::class, 'store']);
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });
    Route::prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/select', [GoalController::class, 'select']);
        Route::get('/my', [GoalController::class, 'my']);
    });
    // İlgi Alanları
    Route::prefix('interests')->group(function () {
        Route::get('/', [InterestController::class, 'index']);
        Route::post('/select', [InterestController::class, 'select']);
        Route::get('/my', [InterestController::class, 'my']);
    });
    Route::prefix('company')->group(function () {
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/', [CompanyController::class, 'show']);
        Route::put('/', [CompanyController::class, 'update']);
    });

    // Buraya ileride diğer route'lar eklenecek:
    // Route::prefix('profile')->group(...)
    // Route::prefix('discover')->group(...)
    // Route::prefix('chat')->group(...)
});
