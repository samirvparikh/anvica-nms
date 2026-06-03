<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AlarmController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ApiRequestLogController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Devices CRUD
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    // Alarms
    Route::get('/alarms', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/alarms/{alarm}/ack', [AlarmController::class, 'acknowledge'])->name('alarms.ack');

    // Maps
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');

    // API Request Logs
    Route::get('/api-request-logs', [ApiRequestLogController::class, 'index'])->name('api-request-logs');
});
