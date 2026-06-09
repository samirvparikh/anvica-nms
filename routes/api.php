<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\DevicePushApiController;

/*
|--------------------------------------------------------------------------
| Public API — callable from router / switch / firewall / server / CCTV / UPS
|--------------------------------------------------------------------------
| Base URL: http://your-nms-host/api/...
| Auth: register device in NMS first; send ip_address + optional X-Api-Key
|       (matches device snmp_community in Devices table)
*/

// Health & test
Route::get('/ping', fn () => response()->json(['status' => 'ok', 'service' => 'Anvica NMS']));
Route::any('/test', [ApiController::class, 'handleTestRequest']);
Route::post('/router', [ApiController::class, 'router']);
Route::any('/router', [ApiController::class, 'router']);

// Device push (router/device → NMS) — flat request_data per endpoint
Route::prefix('device')->group(function () {
    Route::any('/data', [DevicePushApiController::class, 'pushMetricsAndInterfacesData']); //For Both Metrics and Interfaces Data
    Route::any('/metrics', [DevicePushApiController::class, 'metricsEndpoint']);
    Route::any('/interfaces', [DevicePushApiController::class, 'interfaces']);
    // Route::any('/interfaces/data', [DevicePushApiController::class, 'interfacesData']);    
    Route::any('/info', [DevicePushApiController::class, 'info']);
    Route::any('/push', [DevicePushApiController::class, 'push']);
    Route::any('/heartbeat', [DevicePushApiController::class, 'heartbeat']);
});
