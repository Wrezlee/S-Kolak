<?php

use App\Http\Controllers\DashboardPublikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardPublikController::class, 'index'])->name('dashboard');
Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

// Halaman login (GET) — inilah yang dibutuhkan tombol "Masuk"
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Proses login (POST) — dipanggil oleh <form method="POST" action="{{ route('login') }}">
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});