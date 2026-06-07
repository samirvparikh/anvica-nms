<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceMetric;
use App\Services\UserScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringApiController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function dashboardSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $deviceQuery = $this->userScope->devicesQuery($user);

        return response()->json([
            'total_devices' => (clone $deviceQuery)->count(),
            'online_devices' => (clone $deviceQuery)->where('health_status', Device::HEALTH_UP)->count(),
            'offline_devices' => (clone $deviceQuery)->where('health_status', Device::HEALTH_DOWN)->count(),
            'warning_devices' => (clone $deviceQuery)->where('health_status', Device::HEALTH_WARNING)->count(),
            'active_alerts' => $this->userScope->alertsQuery($user)->where('status', Alert::STATUS_OPEN)->count(),
        ]);
    }

    public function deviceMetrics(Request $request, Device $device): JsonResponse
    {
        abort_unless($this->userScope->canAccessDevice($request->user(), $device), 403);

        $metrics = DeviceMetric::where('device_id', $device->id)
            ->latest('recorded_at')
            ->take(50)
            ->get()
            ->groupBy('metric_slug')
            ->map(fn ($group) => $group->first());

        return response()->json([
            'device' => array_merge($device->only(['id', 'name', 'hostname', 'status', 'health_status', 'last_seen']), [
                'health_score' => $device->healthScore(),
            ]),
            'metrics' => $metrics,
        ]);
    }

    public function deviceHealth(Request $request, Device $device): JsonResponse
    {
        abort_unless($this->userScope->canAccessDevice($request->user(), $device), 403);

        return response()->json([
            'device_id' => $device->id,
            'name' => $device->name,
            'hostname' => $device->hostname,
            'status' => $device->status,
            'health_status' => $device->health_status,
            'health_score' => $device->healthScore(),
            'last_seen' => $device->last_seen,
        ]);
    }
}
