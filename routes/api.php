<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    
    // (Public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{slug}', [CourseController::class, 'show']);


    //Sanctum (Protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'changePassword']); 
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        
        // (My Learning & Enrollments)
        Route::get('/my-courses', [\App\Http\Controllers\Api\V1\EnrollmentController::class, 'myCourses']);
        Route::post('/courses/{course}/enroll', [\App\Http\Controllers\Api\V1\EnrollmentController::class, 'enroll']);
        Route::get('/courses/{course}/enrollment-status', [\App\Http\Controllers\Api\V1\EnrollmentController::class, 'checkStatus']);
        // (Lessons & Progress)
        Route::get('/lessons/{lesson}', [\App\Http\Controllers\Api\V1\LessonController::class, 'show']);
        Route::post('/lessons/{lesson}/complete', [\App\Http\Controllers\Api\V1\LessonController::class, 'toggleComplete']);
    });

});