<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use App\Models\Alarm;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@anvica.in'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456'),
                'role' => User::ROLE_ADMIN,
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
                'role' => User::ROLE_USER,
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
                'role' => User::ROLE_USER,
                'is_admin' => false,
                'status' => User::STATUS_ACTIVE,
                'device_limit' => 5,
                'start_date' => now(),
                'expire_date' => now()->addDays(364),
                'created_by' => 1,
            ]
        );


        $samir = \App\Models\User::where('email', 'samir@gmail.com')->first();
        $devices = [
            [
                'user_id' => $samir?->id,
                'service_id' => 1,
                'vendor_id' => 1,
                'name' => 'Anvica_Demo',
                'hostname' => 'Anvica_Demo',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '192.168.5.1',
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Hathijan',
                'status' => 'active',
                'health_status' => 'Up',
            ],
            [
                'user_id' => $samir?->id,
                'service_id' => 1,
                'vendor_id' => 1,
                'name' => 'Anvica_Demo',
                'hostname' => 'Anvica_Demo',
                'type' => 'Router',
                'device_type' => 'Router',
                'ip_address' => '192.168.5.2',
                'snmp_community' => 'Anvica_NMS',
                'location' => 'Hathijan',
                'status' => 'active',
                'health_status' => 'Up',
            ],
        ];

        foreach ($devices as $device) {
            Device::updateOrCreate(['name' => $device['name'], 'ip_address' => $device['ip_address']], $device);
        }

        

        $this->call(MonitoringSeeder::class);
    }
}
