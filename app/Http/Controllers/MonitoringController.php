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
        $isAdmin = (bool) $user->is_admin;
        $customerId = null;
        $selectedCustomer = null;
        $customers = collect();

        if ($isAdmin) {
            $customerId = $request->integer('user_id') ?: null;

            if ($customerId) {
                $selectedCustomer = User::query()
                    ->where('is_admin', false)
                    ->where('role', User::ROLE_USER)
                    ->find($customerId);

                if (! $selectedCustomer) {
                    $customerId = null;
                }
            }

            $customers = User::query()
                ->where('is_admin', false)
                ->where('role', User::ROLE_USER)
                ->orderBy('name')
                ->get();
        }

        $devices = $this->userScope
            ->devicesQuery($user, $isAdmin ? $customerId : null)
            ->with(['user', 'service', 'vendor'])
            ->orderBy('name')
            ->get();

        $scopedInterfaces = $this->userScope
            ->interfacesQuery($user, $isAdmin ? $customerId : null)
            ->orderBy('interface_name')
            ->get()
            ->groupBy('device_id');

        $latestMetrics = $this->userScope->latestMetricsByDevice($user, $isAdmin ? $customerId : null);
        $deviceHealth = $this->userScope->deviceHealthByRecentMetrics($user, $isAdmin ? $customerId : null);

        $interfaces = $this->userScope
            ->interfacesQuery($user, $isAdmin ? $customerId : null)
            ->with(['device.user'])
            ->orderByDesc('updated_at')
            ->get();

        $alerts = $this->userScope
            ->alertsQuery($user, $isAdmin ? $customerId : null)
            ->with(['device.user', 'servicePoint'])
            ->latest()
            ->get();

        return view('monitoring.index', compact(
            'devices',
            'scopedInterfaces',
            'latestMetrics',
            'deviceHealth',
            'interfaces',
            'alerts',
            'customers',
            'customerId',
            'selectedCustomer',
            'isAdmin',
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
            ->metricLogsQuery($request->user(), $customerId)
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
                'metric_text' => $metric->metric_text,
                'recorded_at' => $metric->recorded_at->format('M d, Y H:i:s'),
            ]),
        ]);
    }
}
