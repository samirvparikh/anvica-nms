<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceInterfaceLog;
use App\Models\DeviceMetricLog;
use App\Models\User;
use App\Services\FaultManagementReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaultManagementReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_fault_management_calculates_all_sections_only_from_devices_not_interfaces(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create(['user_id' => $user->id, 'name' => 'Core-Switch']);

        $from = Carbon::now()->subDays(7);
        $to = Carbon::now();

        // 1. Create a device downtime log (ping down)
        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'Ping_Status',
            'metric_value' => 0.0,
            'metric_text' => 'DOWN',
            'recorded_at' => Carbon::now()->subDays(2),
        ]);

        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'Ping_Status',
            'metric_value' => 1.0,
            'metric_text' => 'UP',
            'recorded_at' => Carbon::now()->subDays(2)->addMinutes(30),
        ]);

        // 2. Create an active device CPU alarm (should show up in active alarms)
        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'cpu',
            'metric_value' => 99.0,
            'recorded_at' => Carbon::now()->subMinutes(5),
        ]);

        // 3. Create an active interface down log (should NOT show up in active alarms or downtime events)
        DeviceInterfaceLog::create([
            'device_id' => $device->id,
            'interface_name' => 'GigabitEthernet0/1',
            'status' => 'Down',
            'rx' => 1000,
            'tx' => 2000,
            'recorded_at' => Carbon::now()->subMinutes(2),
        ]);

        // Fetch report
        $service = app(FaultManagementReportService::class);
        $report = $service->build($user, null, $from, $to);

        // Verify downtime summary contains exactly 1 event (the device ping outage)
        // It must NOT contain the interface downtime event (GigabitEthernet0/1).
        $downtimeSummary = collect($report['downtimeSummary']);
        $this->assertCount(1, $downtimeSummary);
        $this->assertEquals('Device Not Responding', $downtimeSummary->first()['reason']);
        $this->assertEquals('Core-Switch', $downtimeSummary->first()['device']);

        // Verify active alarms list contains exactly 1 active alarm (the CPU alarm)
        // It must NOT contain GigabitEthernet0/1.
        $activeAlarms = collect($report['activeAlarms']);
        $this->assertCount(1, $activeAlarms);
        $this->assertEquals('High CPU', $activeAlarms->first()['type']);
        $this->assertEquals('Core-Switch', $activeAlarms->first()['device']);

        // Verify total alerts count KPI is 2 (1 ping alert + 1 cpu alert = 2) and interface is not counted
        $totalAlertsKpi = collect($report['kpis'])->firstWhere('label', 'Total Alerts');
        $this->assertEquals('2', $totalAlertsKpi['value']);
    }
}
