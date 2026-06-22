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
        $deviceQuery = $this->userScope->devicesQuery($user);

        $totalDevices = (clone $deviceQuery)->count();
        $upDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_UP)->count();
        $downDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_DOWN)->count();
        $warningDevices = (clone $deviceQuery)->where('health_status', Device::HEALTH_WARNING)->count();

        $deviceIds = $this->userScope->deviceIds($user);

        $alertQuery = $this->userScope->alertsQuery($user);
        $openAlerts = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->count();
        $openAlarms = Alarm::where('status', 'Open')->count();
        $totalAlarms = $openAlerts + $openAlarms;
        $criticalAlarms = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->where('severity', Alert::SEVERITY_CRITICAL)->count()
            + Alarm::where('status', 'Open')->where('severity', 'Critical')->count();
        $warningAlarms = (clone $alertQuery)->where('status', Alert::STATUS_OPEN)->whereIn('severity', [Alert::SEVERITY_WARNING, Alert::SEVERITY_INFO])->count()
            + Alarm::where('status', 'Open')->where('severity', 'Warning')->count();

        $recentAlerts = $this->alertRepository->recent(4, $user);
        if ($recentAlerts->isEmpty()) {
            $recentAlerts = Alarm::orderByDesc('created_at')->take(4)->get();
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
