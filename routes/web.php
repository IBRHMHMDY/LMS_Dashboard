<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login'); // أو يمكنك توجيهها للـ login مباشرة: return redirect('/login');
});

// مسارات صفحة الدخول الموحدة
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');