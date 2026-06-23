<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_sees_only_own_devices_and_no_dropdown(): void
    {
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $user2 = User::factory()->create(['is_admin' => false, 'role' => 'user']);

        $device1 = Device::factory()->create(['user_id' => $user1->id, 'name' => 'User1-Device']);
        $device2 = Device::factory()->create(['user_id' => $user2->id, 'name' => 'User2-Device']);

        $response = $this->actingAs($user1)->get('/dashboard');

        $response->assertStatus(200);
        
        // Should see user1 device, but not user2 device
        $response->assertSee('User1-Device');
        $response->assertDontSee('User2-Device');

        // Should not see the header user filter dropdown
        $response->assertDontSee('id="headerUserSelect"', false);
    }

    public function test_admin_sees_all_devices_by_default_and_user_filter_dropdown(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Customer Alice']);
        $user2 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Customer Bob']);

        $device1 = Device::factory()->create(['user_id' => $user1->id, 'name' => 'Alice-Router']);
        $device2 = Device::factory()->create(['user_id' => $user2->id, 'name' => 'Bob-Switch']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);

        // Should see both devices by default
        $response->assertSee('Alice-Router');
        $response->assertSee('Bob-Switch');

        // Should see the dropdown filter
        $response->assertSee('id="headerUserSelect"', false);
        $response->assertSee('Customer Alice');
        $response->assertSee('Customer Bob');
    }

    public function test_admin_can_filter_by_specific_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Customer Alice']);
        $user2 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Customer Bob']);

        $device1 = Device::factory()->create(['user_id' => $user1->id, 'name' => 'Alice-Router']);
        $device2 = Device::factory()->create(['user_id' => $user2->id, 'name' => 'Bob-Switch']);

        // Filter dashboard to Alice
        $response = $this->actingAs($admin)->get('/dashboard?user_id=' . $user1->id);

        $response->assertStatus(200);

        // Should see Alice's device, but NOT Bob's device
        $response->assertSee('Alice-Router');
        $response->assertDontSee('Bob-Switch');
        
        // Verify selector shows Alice as selected
        $response->assertSee('value="' . $user1->id . '" selected', false);
    }
}
