<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetManagementCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_assets_and_non_admin_only_sees_own(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Alice']);
        $user2 = User::factory()->create(['is_admin' => false, 'role' => 'user', 'name' => 'Bob']);

        $asset1 = Asset::create([
            'asset_name' => 'Alice-Router',
            'asset_type' => 'Router',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'Medium',
            'manufacturer' => 'Cisco',
            'model_number' => 'ISR 4331',
            'serial_number' => 'SER111',
            'management_ip' => '10.10.1.1',
            'customer_id' => $user1->id,
            'asset_id_auto' => 'AST-2026-0001',
        ]);

        $asset2 = Asset::create([
            'asset_name' => 'Bob-Switch',
            'asset_type' => 'Switch',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'Medium',
            'manufacturer' => 'HP',
            'model_number' => 'ProCurve',
            'serial_number' => 'SER222',
            'management_ip' => '10.10.1.2',
            'customer_id' => $user2->id,
            'asset_id_auto' => 'AST-2026-0002',
        ]);

        // Admin request
        $response = $this->actingAs($admin)->get('/inventory/assets');
        $response->assertStatus(200);
        $response->assertSee('Alice-Router');
        $response->assertSee('Bob-Switch');

        // User1 request (Alice)
        $response = $this->actingAs($user1)->get('/inventory/assets');
        $response->assertStatus(200);
        $response->assertSee('Alice-Router');
        $response->assertDontSee('Bob-Switch');
    }

    public function test_user_can_create_asset_with_scoped_customer_id(): void
    {
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $user2 = User::factory()->create(['is_admin' => false, 'role' => 'user']);

        $response = $this->actingAs($user1)->post('/inventory/assets', [
            'asset_name' => 'Test-Router',
            'asset_type' => 'Router',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'Medium',
            'manufacturer' => 'Cisco',
            'model_number' => 'ISR 4331',
            'serial_number' => 'UNIQUE333',
            'management_ip' => '10.10.1.3',
            'customer_id' => $user2->id, // Attempt to spoof another customer's ID
        ]);

        $response->assertRedirect('/inventory/assets');

        // Verify database contains asset and it is forced to user1's ID
        $this->assertDatabaseHas('assets', [
            'asset_name' => 'Test-Router',
            'serial_number' => 'UNIQUE333',
            'customer_id' => $user1->id, // Verifies security merging of customer_id
        ]);
    }

    public function test_user_can_edit_and_delete_own_asset(): void
    {
        $user1 = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $asset = Asset::create([
            'asset_name' => 'Alice-Router',
            'asset_type' => 'Router',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'Medium',
            'manufacturer' => 'Cisco',
            'model_number' => 'ISR 4331',
            'serial_number' => 'SER111',
            'management_ip' => '10.10.1.1',
            'customer_id' => $user1->id,
            'asset_id_auto' => 'AST-2026-0001',
        ]);

        // Update
        $response = $this->actingAs($user1)->put('/inventory/assets/' . $asset->id, [
            'asset_name' => 'Alice-Router-Updated',
            'asset_type' => 'Router',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'High',
            'manufacturer' => 'Cisco',
            'model_number' => 'ISR 4331',
            'serial_number' => 'SER111',
            'management_ip' => '10.10.1.5',
            'customer_id' => $user1->id,
        ]);

        $response->assertRedirect('/inventory/assets');
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_name' => 'Alice-Router-Updated',
            'criticality' => 'High',
        ]);

        // Delete
        $response = $this->actingAs($user1)->delete('/inventory/assets/' . $asset->id);
        $response->assertRedirect('/inventory/assets');
        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
        ]);
    }
}
