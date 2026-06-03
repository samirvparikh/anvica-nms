<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Alarm;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index()
    {
        $totalDevices = Device::count();
        $upDevices = Device::where('status', 'Up')->count();
        $downDevices = Device::where('status', 'Down')->count();
        $warningDevices = Device::where('status', 'Warning')->count();

        // Alarm stats (Open alarms)
        $totalAlarms = Alarm::where('status', 'Open')->count();
        $criticalAlarms = Alarm::where('status', 'Open')->where('severity', 'Critical')->count();
        $warningAlarms = Alarm::where('status', 'Open')->where('severity', 'Warning')->count();

        // Recent alarms/alerts
        $recentAlerts = Alarm::orderBy('created_at', 'desc')->take(4)->get();

        // Top interfaces (mock utilization data matching screenshots)
        $topInterfaces = [
            ['name' => 'Gig0/1', 'utilization' => 85],
            ['name' => 'Gig0/2', 'utilization' => 72],
            ['name' => 'Gig0/3', 'utilization' => 65],
            ['name' => 'Gig0/4', 'utilization' => 40],
        ];

        return view('dashboard', compact(
            'totalDevices',
            'upDevices',
            'downDevices',
            'warningDevices',
            'totalAlarms',
            'criticalAlarms',
            'warningAlarms',
            'recentAlerts',
            'topInterfaces'
        ));
    }
}
