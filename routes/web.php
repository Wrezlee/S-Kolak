<?php

use App\Http\Controllers\DashboardPublikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KomoditasController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardPublikController::class, 'index'])->name('dashboard');

// Halaman login (GET) — inilah yang dibutuhkan tombol "Masuk"
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Proses login (POST) — dipanggil oleh <form method="POST" action="{{ route('login') }}">
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('index');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Pengguna
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/komoditas', [KomoditasController::class, 'index'])->name('komoditas');
    Route::post('/komoditas', [KomoditasController::class, 'store'])->name('komoditas.store');
    Route::put('/komoditas/{komoditas}', [KomoditasController::class, 'update'])->name('komoditas.update');
    Route::delete('/komoditas/{komoditas}', [KomoditasController::class, 'destroy'])->name('komoditas.destroy');
});