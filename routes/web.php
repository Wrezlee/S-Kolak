<?php

use App\Http\Controllers\DashboardPublikController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardPublikController::class, 'index'])->name('publik.dashboard');