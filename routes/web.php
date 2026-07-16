<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ApplicationMasterController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);



    Route::get('/alarms', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/alarms/{alarm}/ack', [AlarmController::class, 'acknowledge'])->name('alarms.ack');

    Route::get('/alerts', [AlertController::class, 'userIndex'])->name('alerts.index');
    Route::post('/alerts/{alert}/ack', [AlertController::class, 'acknowledge'])->name('alerts.ack');
    Route::post('/alerts/{alert}/close', [AlertController::class, 'close'])->name('alerts.close');

    // Maps
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');

    // Monitoring data (user-scoped; admin sees all)
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/devices/{device}/metrics', [MonitoringController::class, 'deviceMetrics'])->name('monitoring.device.metrics');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/device-management', [ReportController::class, 'deviceManagement'])->name('reports.device-management');
    Route::get('/reports/fault-management', [ReportController::class, 'faultManagement'])->name('reports.fault-management');
    Route::get('/reports/fault-management/data', [ReportController::class, 'faultManagementData'])->name('reports.fault-management.data');
    Route::get('/reports/performance-traffic', [ReportController::class, 'performanceTraffic'])->name('reports.performance-traffic');
    Route::get('/reports/performance-traffic/data', [ReportController::class, 'performanceTrafficData'])->name('reports.performance-traffic.data');
    Route::get('/reports/inventory-sla', [ReportController::class, 'inventorySla'])->name('reports.inventory-sla');
    Route::get('/reports/sla-ticketing', [ReportController::class, 'slaTicketing'])->name('reports.sla-ticketing');
    Route::get('/reports/devices/{device}', [ReportController::class, 'show'])->name('reports.device.show');
    Route::get('/reports/devices/{device}/logs', [ReportController::class, 'deviceLogs'])->name('reports.device.logs');
    Route::get('/reports/devices/{device}/interface-logs', [ReportController::class, 'interfaceLogs'])->name('reports.device.interface.logs');
    Route::get('/reports/devices/{device}/interface-log', [ReportController::class, 'showInterfaceLog'])->name('reports.device.interface.log');
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
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
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

        Route::post('/alarms', [AlarmController::class, 'store'])->name('alarms.store');
        Route::put('/alarms/{alarm}', [AlarmController::class, 'update'])->name('alarms.update');
        Route::delete('/alarms/{alarm}', [AlarmController::class, 'destroy'])->name('alarms.destroy');

        Route::get('/alerts/manage', [AlertController::class, 'index'])->name('alerts.manage');
        Route::post('/alerts/manage', [AlertController::class, 'store'])->name('alerts.store');
        Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
        Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [SettingsController::class, 'testMail'])->name('settings.mail.test');

        Route::get('/master/application-masters', [ApplicationMasterController::class, 'index'])->name('master.application-masters.index');
        Route::post('/master/application-masters', [ApplicationMasterController::class, 'store'])->name('master.application-masters.store');
        Route::put('/master/application-masters/{applicationMaster}', [ApplicationMasterController::class, 'update'])->name('master.application-masters.update');
        Route::delete('/master/application-masters/{applicationMaster}', [ApplicationMasterController::class, 'destroy'])->name('master.application-masters.destroy');

        Route::get('/devices/{device}/script', [DeviceScriptController::class, 'edit'])->name('devices.script.edit');
        Route::put('/devices/{device}/script', [DeviceScriptController::class, 'update'])->name('devices.script.update');
        Route::post('/devices/{device}/script/preview', [DeviceScriptController::class, 'preview'])->name('devices.script.preview');
    });

    // Service Desk Routes
    Route::get('/tickets', [TicketController::class, 'ticketsIndex'])->name('tickets.index');
    Route::get('/incidents', [TicketController::class, 'incidentsIndex'])->name('incidents.index');
    Route::get('/incidents/create', [TicketController::class, 'incidentsCreate'])->name('incidents.create');
    Route::post('/incidents', [TicketController::class, 'incidentsStore'])->name('incidents.store');
    Route::get('/problems', [TicketController::class, 'problemsIndex'])->name('problems.index');
    Route::get('/changes', [TicketController::class, 'changesIndex'])->name('changes.index');
    Route::get('/changes/create', [TicketController::class, 'changesCreate'])->name('changes.create');
    Route::post('/changes', [TicketController::class, 'changesStore'])->name('changes.store');
    Route::get('/knowledge-base', [TicketController::class, 'knowledgeBaseIndex'])->name('knowledge-base.index');

    // Maintenance Routes
    Route::get('/maintenance/preventive', [MaintenanceController::class, 'preventiveIndex'])->name('maintenance.preventive.index');
    Route::get('/maintenance/preventive/create', [MaintenanceController::class, 'preventiveCreate'])->name('maintenance.preventive.create');
    Route::post('/maintenance/preventive', [MaintenanceController::class, 'preventiveStore'])->name('maintenance.preventive.store');
    Route::get('/maintenance/calendar', [MaintenanceController::class, 'calendarIndex'])->name('maintenance.calendar.index');
    Route::get('/maintenance/windows', [MaintenanceController::class, 'windowsIndex'])->name('maintenance.windows.index');

    // Inventory Routes
    Route::get('/inventory/assets', [InventoryController::class, 'assetsIndex'])->name('inventory.assets.index');
    Route::get('/inventory/assets/create', [InventoryController::class, 'assetsCreate'])->name('inventory.assets.create');
    Route::post('/inventory/assets', [InventoryController::class, 'assetsStore'])->name('inventory.assets.store');
    Route::get('/inventory/assets/{asset}/edit', [InventoryController::class, 'assetsEdit'])->name('inventory.assets.edit');
    Route::put('/inventory/assets/{asset}', [InventoryController::class, 'assetsUpdate'])->name('inventory.assets.update');
    Route::delete('/inventory/assets/{asset}', [InventoryController::class, 'assetsDestroy'])->name('inventory.assets.destroy');
    Route::get('/inventory/asset-groups', [InventoryController::class, 'assetGroupsIndex'])->name('inventory.asset-groups.index');
    Route::get('/inventory/software', [InventoryController::class, 'softwareIndex'])->name('inventory.software.index');
    Route::get('/inventory/warranty', [InventoryController::class, 'warrantyIndex'])->name('inventory.warranty.index');
    Route::post('/inventory/warranty', [InventoryController::class, 'warrantyStore'])->name('inventory.warranty.store');

    // SLA Management Routes
    Route::get('/sla/dashboard', [SlaController::class, 'dashboard'])->name('sla.dashboard');
    Route::get('/sla/reports', [SlaController::class, 'reports'])->name('sla.reports');
    Route::get('/sla/targets', [SlaController::class, 'targets'])->name('sla.targets');
    Route::get('/sla/maintenance', [SlaController::class, 'maintenance'])->name('sla.maintenance');
});

