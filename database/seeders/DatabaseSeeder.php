<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceVendor;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Support\DeviceAssetMapper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ApplicationMasterSeeder::class);
        $this->call(RoleSeeder::class);

        $superadminRole = Role::findBySlug(Role::SLUG_SUPERADMIN);
        $engineerRole = Role::findBySlug(Role::SLUG_ENGINEER);

        User::updateOrCreate(
            ['email' => 'admin@anvica.in'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456'),
                'role_id' => $superadminRole?->id,
                'is_admin' => true,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        User::updateOrCreate(
            ['email' => 'samir@gmail.com'],
            [
                'name' => 'samir',
                'mobile' => '9898183457',
                'password' => Hash::make('123456'),
                'role_id' => $engineerRole?->id,
                'is_admin' => false,
                'status' => User::STATUS_ACTIVE,
                'device_limit' => 5,
                'start_date' => now(),
                'expire_date' => now()->addDays(364),
                'created_by' => 1,
            ]
        );

        User::updateOrCreate(
            ['email' => 'vijay@gmail.com'],
            [
                'name' => 'vijay',
                'mobile' => '9898183458',
                'password' => Hash::make('123456'),
                'role_id' => $engineerRole?->id,
                'is_admin' => false,
                'status' => User::STATUS_ACTIVE,
                'device_limit' => 5,
                'start_date' => now(),
                'expire_date' => now()->addDays(364),
                'created_by' => 1,
            ]
        );

        User::updateOrCreate(
            ['email' => 'jatin@gmail.com'],
            [
                'name' => 'jatin',
                'mobile' => '9898183459',
                'password' => Hash::make('123456'),
                'role_id' => $engineerRole?->id,
                'is_admin' => false,
                'status' => User::STATUS_ACTIVE,
                'device_limit' => 5,
                'start_date' => now(),
                'expire_date' => now()->addDays(364),
                'created_by' => 1,
            ]
        );


        $samir = User::where('email', 'samir@gmail.com')->first();
        $jatin = User::where('email', 'jatin@gmail.com')->first();

        $this->call(MonitoringSeeder::class);

        $routerService = Service::where('slug', 'router')->first();
        $mikrotikVendor = $routerService
            ? DeviceVendor::where('service_id', $routerService->id)->where('slug', 'mikrotik')->first()
            : null;

        $devices = [
            [
                'user_id' => $samir?->id,
                'service_id' => $routerService?->id,
                'vendor_id' => $mikrotikVendor?->id,
                'name' => 'Anvica_Demo',
                'hostname' => 'Anvica_Demo',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '192.168.5.1',
                'snmp_version' => '2c',
                'snmp_port' => 161,
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Hathijan',
                'status' => 'active',
                'health_status' => 'Up',
            ],
            [
                'user_id' => $samir?->id,
                'service_id' => $routerService?->id,
                'vendor_id' => $mikrotikVendor?->id,
                'name' => 'Anvica_Demo',
                'hostname' => 'Anvica_Demo2',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '192.168.5.2',
                'snmp_version' => '2c',
                'snmp_port' => 161,
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Hathijan',
                'status' => 'active',
                'health_status' => 'Up',
            ],
            [
                'user_id' => $samir?->id,
                'service_id' => $routerService?->id,
                'vendor_id' => $mikrotikVendor?->id,
                'name' => 'Anvica_Demo',
                'hostname' => 'Anvica_Demo3',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '192.168.5.3',
                'snmp_version' => '2c',
                'snmp_port' => 161,
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Hathijan',
                'status' => 'active',
                'health_status' => 'Up',
            ],
            [
                'user_id' => $jatin?->id,
                'service_id' => $routerService?->id,
                'vendor_id' => $mikrotikVendor?->id,
                'name' => 'Anvica_Jatin',
                'hostname' => 'MikroTik',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '103.112.225.1',
                'snmp_version' => '2c',
                'snmp_port' => 161,
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Jamnagar',
                'status' => 'active',
                'health_status' => 'Up',
            ],
        ];

        foreach ($devices as $device) {
            $payload = DeviceAssetMapper::fromLegacyArray($device);

            $existing = Device::where('management_ip', $payload['management_ip'])->first();
            if ($existing) {
                unset($payload['asset_id_auto'], $payload['serial_number']);
            }

            Device::updateOrCreate(
                ['management_ip' => $payload['management_ip']],
                $payload
            );
        }

        $this->call(SlaSeeder::class);
    }
}
