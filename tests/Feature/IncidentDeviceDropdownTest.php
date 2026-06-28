<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use App\Models\SlaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentDeviceDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_users_and_devices_on_incident_create(): void
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        $device1 = Device::factory()->create(['user_id' => $user1->id, 'name' => 'Alice Device']);
        $device2 = Device::factory()->create(['user_id' => $user2->id, 'name' => 'Bob Device']);

        $response = $this->actingAs($admin)->get('/incidents/create');

        $response->assertStatus(200);

        // Verify select dropdown exists for admin
        $response->assertSee('id="customer_id"', false);
        $response->assertSee('Alice');
        $response->assertSee('Bob');

        // Check if devices are present
        $response->assertSee('Alice Device');
        $response->assertSee('Bob Device');
    }

    public function test_non_admin_sees_prefilled_customer_and_only_own_devices(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        $device1 = Device::factory()->create(['user_id' => $user1->id, 'name' => 'Alice Device']);
        $device2 = Device::factory()->create(['user_id' => $user2->id, 'name' => 'Bob Device']);

        $response = $this->actingAs($user1)->get('/incidents/create');

        $response->assertStatus(200);

        // Should see user1's device but not user2's device
        $response->assertSee('Alice Device');
        $response->assertDontSee('Bob Device');

        // Verify hidden input exists for customer_id with user1's id
        $response->assertSee('name="customer_id" id="customer_id" value="' . $user1->id . '"', false);
        // Verify disabled text showing name/email exists
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_non_admin_customer_id_is_merged_and_forced_to_own_id_on_store(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        SlaPolicy::create([
            'name' => 'Standard Incident SLA',
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 120,
        ]);

        // Attempting to post customer_id as user2, even though logged in as user1
        $response = $this->actingAs($user1)->post('/incidents', [
            'title' => 'Test VPN Failure',
            'customer_id' => $user2->id,
            'priority' => 'medium',
        ]);

        $response->assertRedirect('/incidents');
        
        // Assert ticket was created with customer_id of user1 (merged/forced)
        $this->assertDatabaseHas('tickets', [
            'title' => 'Test VPN Failure',
            'customer_id' => $user1->id,
            'type' => 'incident',
        ]);
    }
}
