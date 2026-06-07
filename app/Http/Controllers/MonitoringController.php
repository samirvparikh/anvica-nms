<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Services\UserScopeService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $customerId = $user->isAdmin() ? $request->integer('user_id') ?: null : null;

        $devices = $this->userScope
            ->devicesQuery($user, $customerId)
            ->with(['user', 'service', 'vendor'])
            ->orderBy('name')
            ->get();

        $latestMetrics = $this->userScope->latestMetricsByDevice($user, $customerId);

        $interfaces = $this->userScope
            ->interfacesQuery($user, $customerId)
            ->with(['device.user'])
            ->orderByDesc('updated_at')
            ->get();

        $alerts = $this->userScope
            ->alertsQuery($user, $customerId)
            ->with(['device.user', 'servicePoint'])
            ->latest()
            ->get();

        $customers = $user->isAdmin()
            ? User::where('is_admin', false)->where('role', User::ROLE_USER)->orderBy('name')->get()
            : collect();

        return view('monitoring.index', compact(
            'devices',
            'latestMetrics',
            'interfaces',
            'alerts',
            'customers',
            'customerId',
        ));
    }

    public function deviceMetrics(Request $request, Device $device)
    {
        if (! $this->userScope->canAccessDevice($request->user(), $device)) {
            abort(403);
        }

        $customerId = $request->user()->isAdmin()
            ? ($request->integer('user_id') ?: null)
            : null;

        $metrics = $this->userScope
            ->metricsQuery($request->user(), $customerId)
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->orderBy('metric_slug')
            ->limit(1000)
            ->get();

        return response()->json([
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
            ],
            'metrics' => $metrics->map(fn ($metric) => [
                'metric_slug' => $metric->metric_slug,
                'metric_value' => $metric->metric_value,
                'recorded_at' => $metric->recorded_at->format('M d, Y H:i:s'),
            ]),
        ]);
    }
}
