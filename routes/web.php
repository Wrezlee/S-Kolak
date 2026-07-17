<?php

use App\Http\Controllers\DashboardPublikController;
use App\Http\Controllers\Auth\LoginController;
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