<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])
    ->name('root');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::get('/scan', [AuthController::class, 'showScan'])
    ->name('scan');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/history', [HistoryController::class, 'index'])
    ->name('history');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');