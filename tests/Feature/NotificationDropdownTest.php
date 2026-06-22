<?php

namespace Tests\Feature;

use App\Models\Alarm;
use App\Models\Alert;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_notification_badge_and_dropdown(): void
    {
        $user = User::factory()->create();

        // Create an alert and associate it with a device
        $device = Device::factory()->create();
        // Link user to device if there is a scope mapping
        $device->user()->associate($user);
        $device->save();

        $alert = Alert::factory()->create([
            'device_id' => $device->id,
            'status' => Alert::STATUS_OPEN,
            'message' => 'Custom Test Alert Message',
            'severity' => Alert::SEVERITY_CRITICAL,
        ]);

        // Create an alarm
        $alarm = Alarm::create([
            'device_name' => 'Test Alarm Device',
            'message' => 'Custom Test Alarm Message',
            'severity' => 'Critical',
            'status' => 'Open',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify HTML layout components are present
        $response->assertSee('id="notificationWidget"', false);
        $response->assertSee('id="notificationTrigger"', false);
        $response->assertSee('id="notificationDropdown"', false);

        // Verify dynamic notifications are rendered
        $response->assertSee('Custom Test Alert Message');
        $response->assertSee('Custom Test Alarm Message');
        
        // Count total active (1 alert + 1 alarm = 2 active)
        $response->assertSee('2 Active');
    }
}
