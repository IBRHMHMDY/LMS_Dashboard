<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    
    // مسارات المصادقة (Public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{slug}', [CourseController::class, 'show']);


    // المسارات المحمية بـ Sanctum (Protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        
        // سيتم إضافة مسارات الكورسات والدروس هنا لاحقاً...
    });

});