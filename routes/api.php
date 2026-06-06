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
// Route::any('/test', [ApiController::class, 'handleTestRequest']);
Route::any('/test', [ApiController::class, 'router']);
Route::post('/router', [ApiController::class, 'router']);
Route::any('/router', [ApiController::class, 'router']);

// Device push (router/device → NMS)
Route::prefix('device')->group(function () {
    Route::get('/info', [DevicePushApiController::class, 'info']);
    Route::get('/metrics', [DevicePushApiController::class, 'latestMetrics']);
    Route::post('/push', [DevicePushApiController::class, 'push']);
    Route::post('/metrics', [DevicePushApiController::class, 'metrics']);
    Route::post('/interfaces', [DevicePushApiController::class, 'interfaces']);
    Route::post('/heartbeat', [DevicePushApiController::class, 'heartbeat']);
});
