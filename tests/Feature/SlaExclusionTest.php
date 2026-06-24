<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\MaintenanceWindow;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_deadlines_calculate_correctly_without_maintenance_window()
    {
        $policy = SlaPolicy::create([
            'name' => 'Standard SLA',
            'response_time_minutes' => 30,
            'resolution_time_minutes' => 180,
        ]);

        $ticket = new Ticket([
            'title' => 'Test Ticket',
            'sla_policy_id' => $policy->id,
        ]);
        $ticket->created_at = Carbon::now();
        $ticket->calculateSlaDeadlines();

        $this->assertEquals($ticket->created_at->copy()->addMinutes(30)->timestamp, $ticket->response_sla_deadline->timestamp);
        $this->assertEquals($ticket->created_at->copy()->addMinutes(180)->timestamp, $ticket->resolution_sla_deadline->timestamp);
    }

    public function test_sla_deadlines_are_paused_and_shifted_during_exclude_sla_maintenance_window()
    {
        $device = Device::create([
            'name' => 'Test Router',
            'ip_address' => '10.0.0.1',
            'type' => 'Router',
            'device_type' => 'Router',
            'location' => 'Hathijan',
            'status' => 'active',
        ]);

        $policy = SlaPolicy::create([
            'name' => 'Gold SLA',
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 120,
        ]);

        // Create an overlapping maintenance window that excludes SLA
        $window = MaintenanceWindow::create([
            'title' => 'Scheduled Upgrade',
            'maintenance_id' => 'PM-TEST-01',
            'primary_device_id' => $device->id,
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addMinutes(50), // 60 minutes expected downtime
            'expected_downtime_minutes' => 60,
            'exclude_sla' => true,
            'status' => 'approved',
        ]);

        $ticket = new Ticket([
            'title' => 'Critical Failure',
            'device_id' => $device->id,
            'sla_policy_id' => $policy->id,
        ]);
        $ticket->created_at = Carbon::now();
        $ticket->calculateSlaDeadlines();

        // Target should be shifted by 60 minutes
        $expectedResponse = $ticket->created_at->copy()->addMinutes(15)->addMinutes(60);
        $expectedResolution = $ticket->created_at->copy()->addMinutes(120)->addMinutes(60);

        $this->assertEquals($expectedResponse->timestamp, $ticket->response_sla_deadline->timestamp);
        $this->assertEquals($expectedResolution->timestamp, $ticket->resolution_sla_deadline->timestamp);
    }
}
