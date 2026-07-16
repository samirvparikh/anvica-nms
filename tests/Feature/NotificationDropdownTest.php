<?php

namespace Tests\Feature;

use App\Models\Alarm;
use App\Models\Alert;
use App\Models\User;
use App\Services\AlertToAlarmConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_alert_and_alarm_notification_widgets(): void
    {
        $user = User::factory()->create();
        $deviceId = $this->createAsset($user->id, 'Alert Device');
        $this->createAlert($deviceId, 'Custom Test Alert Message', 'critical');

        Alarm::create([
            'device_name' => 'Test Alarm Device',
            'message' => 'Custom Test Alarm Message',
            'severity' => 'Critical',
            'status' => 'Open',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('id="alertNotificationWidget"', false);
        $response->assertSee('id="alarmNotificationWidget"', false);
        $response->assertSee('Custom Test Alert Message');
        $response->assertSee('Custom Test Alarm Message');
        $response->assertSee('View All Alerts');
        $response->assertSee('View All Alarms');
    }

    public function test_unacknowledged_alert_converts_to_alarm_after_15_minutes(): void
    {
        $user = User::factory()->create();
        $deviceId = $this->createAsset($user->id, 'Convert Device');
        $alertId = $this->createAlert(
            $deviceId,
            'Unacked alert converts',
            'warning',
            now()->subMinutes(16)
        );

        $converted = app(AlertToAlarmConverter::class)->convertExpiredAlerts();

        $this->assertSame(1, $converted);
        $this->assertDatabaseHas('alarms', [
            'alert_id' => $alertId,
            'message' => 'Unacked alert converts',
            'severity' => 'Warning',
            'status' => 'Open',
        ]);

        $alert = Alert::find($alertId);
        $this->assertNotNull($alert?->converted_to_alarm_at);
        $this->assertSame(Alert::STATUS_CLOSED, $alert->status);
    }

    protected function createAsset(int $customerId, string $name): int
    {
        $now = now();

        return (int) DB::table('assets')->insertGetId([
            'asset_name' => $name,
            'hostname' => $name,
            'management_ip' => '10.0.0.'.random_int(1, 200),
            'model_number' => 'Generic',
            'serial_number' => 'SN-'.uniqid(),
            'asset_id_auto' => 'AST-TEST-'.uniqid(),
            'customer_id' => $customerId,
            'health_status' => 'Up',
            'snmp_port' => 161,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function createAlert(int $deviceId, string $message, string $severity, $createdAt = null): int
    {
        $createdAt = $createdAt ?? now();
        $id = 0;

        Schema::withoutForeignKeyConstraints(function () use ($deviceId, $message, $severity, $createdAt, &$id) {
            $id = (int) DB::table('alerts')->insertGetId([
                'device_id' => $deviceId,
                'alarm_type' => 'Threshold Violation',
                'severity' => $severity,
                'message' => $message,
                'status' => Alert::STATUS_OPEN,
                'started_at' => $createdAt,
                'acknowledged_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        });

        return $id;
    }
}
