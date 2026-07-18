<?php

use App\Http\Controllers\DashboardPublikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KomoditasController;
use App\Http\Controllers\Admin\DataNeracaController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\NotifikasiController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\NeracaPanganController as OperatorNeracaPanganController;
use App\Http\Controllers\Operator\LaporanController as OperatorLaporanController;
use App\Http\Controllers\Operator\NotifikasiController as OperatorNotifikasiController;
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

    // Data Neraca Pangan
    Route::get('/data', [DataNeracaController::class, 'index'])->name('data');
    Route::delete('/data/{neracaPangan}', [DataNeracaController::class, 'destroy'])->name('data.destroy');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::patch('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.baca');
    Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.baca-semua');
});

Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {
    Route::redirect('/', '/operator/dashboard')->name('index');

    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');

    // Input Neraca Pangan
    Route::get('/input', [OperatorNeracaPanganController::class, 'create'])->name('input');
    Route::post('/input', [OperatorNeracaPanganController::class, 'store'])->name('input.store');
    Route::get('/input/stok-awal', [OperatorNeracaPanganController::class, 'stokAwal'])->name('input.stok-awal');

    // Data Neraca Saya (list milik operator + perbaikan data revisi)
    Route::get('/data', [OperatorNeracaPanganController::class, 'index'])->name('data');
    Route::put('/data/{neracaPangan}', [OperatorNeracaPanganController::class, 'update'])->name('data.update');

    Route::get('/laporan', [OperatorLaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/cetak', [OperatorLaporanController::class, 'cetak'])->name('laporan.cetak');

    // Notifikasi
    Route::get('/notifikasi', [OperatorNotifikasiController::class, 'index'])->name('notifikasi');
    Route::patch('/notifikasi/{notifikasi}/baca', [OperatorNotifikasiController::class, 'markAsRead'])->name('notifikasi.baca');
    Route::patch('/notifikasi/baca-semua', [OperatorNotifikasiController::class, 'markAllAsRead'])->name('notifikasi.baca-semua');
});