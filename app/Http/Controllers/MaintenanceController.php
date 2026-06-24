<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MaintenanceWindow;
use App\Models\SlaPolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function preventiveIndex(Request $request)
    {
        $query = MaintenanceWindow::with('device');

        // Sorting
        $sort = $request->query('sort', 'start_time');
        $dir = $request->query('direction', 'asc');
        if (in_array($sort, ['maintenance_id', 'title', 'start_time', 'end_time', 'status'])) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $maintenances = $query->get();

        if ($maintenances->isEmpty()) {
            $maintenances = $this->getMockMaintenances();
        }

        return view('maintenance.preventive.index', compact('maintenances', 'sort', 'dir'));
    }

    public function preventiveCreate()
    {
        $devices = Device::all();
        $policies = SlaPolicy::all();
        $users = User::all();
        return view('maintenance.preventive.create', compact('devices', 'policies', 'users'));
    }

    public function preventiveStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'primary_device_id' => 'required|exists:devices,id',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $start = Carbon::parse($request->input('start_time'));
        $end = Carbon::parse($request->input('end_time'));
        $expectedDowntime = $end->diffInMinutes($start);

        MaintenanceWindow::create([
            'title' => $request->input('title'),
            'maintenance_id' => 'PM-' . Carbon::now()->format('Y') . '-' . mt_rand(10000, 99999),
            'type' => $request->input('type', 'Preventive'),
            'category' => $request->input('category', 'Network'),
            'primary_device_id' => $request->input('primary_device_id'),
            'start_time' => $start,
            'end_time' => $end,
            'expected_downtime_minutes' => (int) $request->input('expected_downtime_minutes', $expectedDowntime),
            'exclude_sla' => $request->has('exclude_sla'),
            'sla_impact' => $request->input('sla_impact', 'No Breach (Maintenance)'),
            'sla_policy' => $request->input('sla_policy', 'Standard Incident SLA'),
            'notify_before_minutes' => (int) $request->input('notify_before_minutes', 120),
            'notification_recipients' => $request->input('notification_recipients'),
            'implementation_steps' => $request->input('implementation_steps'),
            'rollback_plan' => $request->input('rollback_plan'),
            'notify_users' => $request->has('notify_users'),
            'notification_method' => $request->input('notification_method', 'Email'),
            'notification_message' => $request->input('notification_message'),
            'requested_by' => $request->input('requested_by'),
            'approved_noc_manager' => $request->input('approved_noc_manager'),
            'approved_it_head' => $request->input('approved_it_head'),
            'customer_approval' => $request->input('customer_approval', 'Pending'),
            'status' => 'scheduled',
        ]);

        return redirect()->route('maintenance.preventive.index')->with('success', 'Preventive maintenance window scheduled successfully.');
    }

    public function calendarIndex()
    {
        $events = MaintenanceWindow::with('device')->get();
        if ($events->isEmpty()) {
            $events = $this->getMockMaintenances();
        }
        return view('maintenance.calendar.index', compact('events'));
    }

    public function windowsIndex()
    {
        $windows = MaintenanceWindow::with('device')->latest()->get();
        if ($windows->isEmpty()) {
            $windows = $this->getMockMaintenances();
        }
        return view('maintenance.windows.index', compact('windows'));
    }

    protected function getMockMaintenances()
    {
        // Check if we have devices, otherwise create a mock device structure
        $device = Device::first() ?? (object)['id' => 1, 'name' => 'Core-Router-01'];

        return collect([
            (object)[
                'id' => 1,
                'maintenance_id' => 'PM-2026-00045',
                'title' => 'Core Router Firmware Upgrade',
                'type' => 'Preventive',
                'category' => 'Network',
                'device' => $device,
                'primary_device_id' => $device->id,
                'start_time' => Carbon::now()->addDays(2)->setTime(23, 0, 0),
                'end_time' => Carbon::now()->addDays(3)->setTime(1, 0, 0),
                'expected_downtime_minutes' => 120,
                'status' => 'scheduled',
                'exclude_sla' => true,
                'sla_impact' => 'No Breach (Maintenance)',
                'sla_policy' => 'Standard Incident SLA',
                'requested_by' => 'Admin User',
                'approved_noc_manager' => 'Vijay Kumar',
                'approved_it_head' => 'Rajesh Sharma',
                'customer_approval' => 'Approved',
            ],
            (object)[
                'id' => 2,
                'maintenance_id' => 'PM-2026-00048',
                'title' => 'Firewall Rules Clean-up & Sync',
                'type' => 'Preventive',
                'category' => 'Security',
                'device' => $device,
                'primary_device_id' => $device->id,
                'start_time' => Carbon::now()->addDays(5)->setTime(22, 0, 0),
                'end_time' => Carbon::now()->addDays(5)->setTime(23, 0, 0),
                'expected_downtime_minutes' => 60,
                'status' => 'scheduled',
                'exclude_sla' => true,
                'sla_impact' => 'No Breach (Maintenance)',
                'sla_policy' => 'Standard Incident SLA',
                'requested_by' => 'Admin User',
                'approved_noc_manager' => 'Vijay Kumar',
                'approved_it_head' => 'Rajesh Sharma',
                'customer_approval' => 'Pending',
            ]
        ]);
    }
}
