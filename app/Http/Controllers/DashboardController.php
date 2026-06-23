<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Alarm;
use App\Repositories\AlertRepository;
use App\Models\Device;
use App\Models\DeviceMetric;
use App\Services\UserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
        protected AlertRepository $alertRepository,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $customerId = $request->query('user_id');
        if (!$user->isAdmin()) {
            $customerId = null;
        }

        $deviceQuery = $this->userScope->devicesQuery($user, $customerId);

        $totalDevices = (clone $deviceQuery)->count();
        $upDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_UP)->count();
        $downDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_DOWN)->count();
        $warningDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_WARNING)->count();

        $deviceIds = $this->userScope->deviceIds($user, $customerId);

        $alertQuery = $this->userScope->alertsQuery($user, $customerId);
        $openAlerts = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->count();
        
        $deviceNames = (clone $deviceQuery)->pluck('name')->all();
        $openAlarmsQuery = Alarm::where('status', 'Open');
        $criticalAlarmsQuery = Alarm::where('status', 'Open')->where('severity', 'Critical');
        $warningAlarmsQuery = Alarm::where('status', 'Open')->where('severity', 'Warning');

        if (!$user->isAdmin() || $customerId) {
            $openAlarmsQuery->whereIn('device_name', $deviceNames);
            $criticalAlarmsQuery->whereIn('device_name', $deviceNames);
            $warningAlarmsQuery->whereIn('device_name', $deviceNames);
        }

        $openAlarms = $openAlarmsQuery->count();
        $totalAlarms = $openAlerts + $openAlarms;
        $criticalAlarms = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->where('severity', Alert::SEVERITY_CRITICAL)->count()
            + $criticalAlarmsQuery->count();
        $warningAlarms = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->whereIn('severity', [Alert::SEVERITY_WARNING, Alert::SEVERITY_INFO])->count()
            + $warningAlarmsQuery->count();

        $recentAlerts = (clone $alertQuery)
            ->with('device')
            ->latest()
            ->take(4)
            ->get();

        if ($recentAlerts->isEmpty()) {
            $recentAlarmsQuery = Alarm::orderByDesc('created_at');
            if (!$user->isAdmin() || $customerId) {
                $recentAlarmsQuery->whereIn('device_name', $deviceNames);
            }
            $recentAlerts = $recentAlarmsQuery->take(4)->get();
        }

        $cpuTrend = DeviceMetric::select('metric_value', 'recorded_at')
            ->whereIn('device_id', $deviceIds)
            ->where('metric_slug', 'cpu')
            ->latest('recorded_at')
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $ramTrend = DeviceMetric::select('metric_value', 'recorded_at')
            ->whereIn('device_id', $deviceIds)
            ->where('metric_slug', 'ram')
            ->latest('recorded_at')
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $trafficTrend = DeviceMetric::select(DB::raw('AVG(metric_value) as metric_value'), 'recorded_at')
            ->whereIn('device_id', $deviceIds)
            ->where('metric_slug', 'traffic')
            ->groupBy('recorded_at')
            ->orderBy('recorded_at')
            ->take(12)
            ->get();

        $temperatureTrend = DeviceMetric::select('metric_value', 'recorded_at')
            ->whereIn('device_id', $deviceIds)
            ->where('metric_slug', 'temperature')
            ->latest('recorded_at')
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $devices = (clone $deviceQuery)->with(['service', 'vendor'])->take(10)->get();
        $healthScores = $devices->map(fn (Device $device) => [
            'name' => $device->name,
            'score' => $device->healthScore(),
        ]);

        $topInterfaces = Device::with('interfaces')
            ->whereIn('id', $deviceIds)
            ->get()
            ->flatMap(fn (Device $device) => $device->interfaces->map(fn ($iface) => [
                'name' => $device->name . ' / ' . $iface->interface_name,
                'utilization' => min(100, (int) (($iface->rx + $iface->tx) / 50000)),
            ]))
            ->sortByDesc('utilization')
            ->take(4)
            ->values()
            ->all();

        if (empty($topInterfaces)) {
            $topInterfaces = [
                ['name' => 'Gig0/1', 'utilization' => 85],
                ['name' => 'Gig0/2', 'utilization' => 72],
                ['name' => 'Gig0/3', 'utilization' => 65],
                ['name' => 'Gig0/4', 'utilization' => 40],
            ];
        }

        return view('dashboard', compact(
            'totalDevices',
            'upDevices',
            'downDevices',
            'warningDevices',
            'totalAlarms',
            'criticalAlarms',
            'warningAlarms',
            'recentAlerts',
            'topInterfaces',
            'cpuTrend',
            'ramTrend',
            'trafficTrend',
            'temperatureTrend',
            'healthScores',
        ));
    }
}
