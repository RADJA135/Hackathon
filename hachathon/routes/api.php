<?php

use App\Http\Controllers\Api\DecisionController;
use App\Http\Controllers\Api\DeviceStatusController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SimSwapController;
use Illuminate\Support\Facades\Route;

// The 3 Nokia signal endpoints — one per teammate
Route::post('/nokia/sim-swap', [SimSwapController::class, 'check']);       // Radja
Route::post('/nokia/device-status', [DeviceStatusController::class, 'check']); // Haddad
Route::post('/nokia/location', [LocationController::class, 'check']);      // Semsoum

// Calls the Python AI agent once all 3 signals are collected
Route::post('/decision', [DecisionController::class, 'decide']);
