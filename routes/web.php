<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceScriptController;
use App\Http\Controllers\AlarmController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ApiRequestLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServicePointController;
use App\Http\Controllers\DeviceVendorController;
use App\Http\Controllers\VendorScriptController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Api\MonitoringApiController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ReportController;

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

    // Alarms (legacy view)
    Route::get('/alarms', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/alarms/{alarm}/ack', [AlarmController::class, 'acknowledge'])->name('alarms.ack');

    // Maps
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');

    // Monitoring data (user-scoped; admin sees all)
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/devices/{device}/metrics', [MonitoringController::class, 'deviceMetrics'])->name('monitoring.device.metrics');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/devices/{device}', [ReportController::class, 'show'])->name('reports.device.show');
    Route::get('/reports/devices/{device}/logs', [ReportController::class, 'deviceLogs'])->name('reports.device.logs');
    Route::get('/reports/devices/{device}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.device.export.excel');
    Route::get('/reports/devices/{device}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.device.export.pdf');

    // API Request Logs
    Route::get('/api-request-logs', [ApiRequestLogController::class, 'index'])->name('api-request-logs');
    Route::get('/api-request-logs/{apiRequestLog}', [ApiRequestLogController::class, 'show'])->name('api-request-logs.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Monitoring API (session auth)
    Route::prefix('api/monitoring')->group(function () {
        Route::get('/dashboard', [MonitoringApiController::class, 'dashboardSummary']);
        Route::get('/devices/{device}/metrics', [MonitoringApiController::class, 'deviceMetrics']);
        Route::get('/devices/{device}/health', [MonitoringApiController::class, 'deviceHealth']);
    });

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

        Route::get('/service-points', [ServicePointController::class, 'index'])->name('service-points.index');
        Route::post('/service-points', [ServicePointController::class, 'store'])->name('service-points.store');
        Route::put('/service-points/{servicePoint}', [ServicePointController::class, 'update'])->name('service-points.update');
        Route::delete('/service-points/{servicePoint}', [ServicePointController::class, 'destroy'])->name('service-points.destroy');

        Route::get('/vendors', [DeviceVendorController::class, 'index'])->name('vendors.index');
        Route::post('/vendors', [DeviceVendorController::class, 'store'])->name('vendors.store');
        Route::put('/vendors/{vendor}', [DeviceVendorController::class, 'update'])->name('vendors.update');
        Route::delete('/vendors/{vendor}', [DeviceVendorController::class, 'destroy'])->name('vendors.destroy');
        Route::get('/vendors/{vendor}/script', [VendorScriptController::class, 'edit'])->name('vendors.script.edit');
        Route::put('/vendors/{vendor}/script', [VendorScriptController::class, 'update'])->name('vendors.script.update');

        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
        Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
        Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');
        Route::post('/alerts/{alert}/close', [AlertController::class, 'close'])->name('alerts.close');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [SettingsController::class, 'testMail'])->name('settings.mail.test');

        Route::get('/devices/{device}/script', [DeviceScriptController::class, 'edit'])->name('devices.script.edit');
        Route::put('/devices/{device}/script', [DeviceScriptController::class, 'update'])->name('devices.script.update');
        Route::post('/devices/{device}/script/preview', [DeviceScriptController::class, 'preview'])->name('devices.script.preview');
    });
});
