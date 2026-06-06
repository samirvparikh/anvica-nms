<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AlarmController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ApiRequestLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;

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
    Route::get('/api-request-logs/{apiRequestLog}', [ApiRequestLogController::class, 'show'])->name('api-request-logs.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [SettingsController::class, 'testMail'])->name('settings.mail.test');
    });
});
