<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use App\Models\Alarm;
use App\Models\Site;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Administrator User
        User::updateOrCreate(
            ['email' => 'admin@anvica.in'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Seed sample services
        $networkMonitoring = Service::updateOrCreate(
            ['name' => 'Network Monitoring'],
            ['name' => 'Network Monitoring']
        );
        $networkMonitoring->points()->delete();
        $networkMonitoring->points()->createMany([
            ['point' => 'CPU Usage', 'method' => 'SNMP'],
            ['point' => 'Memory Usage', 'method' => 'SNMP'],
            ['point' => 'Interface Status', 'method' => 'API'],
        ]);

        $uptimeCheck = Service::updateOrCreate(
            ['name' => 'Uptime Check'],
            ['name' => 'Uptime Check']
        );
        $uptimeCheck->points()->delete();
        $uptimeCheck->points()->createMany([
            ['point' => 'Ping Response', 'method' => 'METHOD'],
            ['point' => 'Availability', 'method' => 'API'],
        ]);

        // Seed Devices
        $devices = [
            [
                'name' => 'Core-Switch01',
                'type' => 'Switch',
                'ip_address' => '10.0.0.1',
                'location' => 'Data Center',
                'status' => 'Up',
            ],
            [
                'name' => 'Firewall-01',
                'type' => 'Firewall',
                'ip_address' => '10.0.0.2',
                'location' => 'Edge',
                'status' => 'Warning',
            ],
            [
                'name' => 'Router-WAN',
                'type' => 'Router',
                'ip_address' => '10.0.0.3',
                'location' => 'Edge',
                'status' => 'Up',
            ],
            [
                'name' => 'AP-Floor3',
                'type' => 'Access Point',
                'ip_address' => '10.0.3.21',
                'location' => 'Floor 3',
                'status' => 'Down',
            ],
            [
                'name' => 'Server-02',
                'type' => 'Server',
                'ip_address' => '10.0.1.10',
                'location' => 'Rack B2',
                'status' => 'Warning',
            ],
            [
                'name' => 'CCTV-Lobby',
                'type' => 'CCTV',
                'ip_address' => '10.0.4.5',
                'location' => 'Lobby',
                'status' => 'Up',
            ],
            [
                'name' => 'UPS-Main',
                'type' => 'UPS',
                'ip_address' => '10.0.5.1',
                'location' => 'Server Room',
                'status' => 'Up',
            ],
        ];

        foreach ($devices as $device) {
            Device::updateOrCreate(['name' => $device['name']], $device);
        }

        // Seed Alarms
        $alarms = [
            [
                'device_name' => 'Core-Switch01',
                'message' => 'Link Down on Gi0/1',
                'severity' => 'Critical',
                'status' => 'Open',
            ],
            [
                'device_name' => 'Firewall-01',
                'message' => 'CPU > 95%',
                'severity' => 'Critical',
                'status' => 'Open',
            ],
            [
                'device_name' => 'Server-02',
                'message' => 'Disk Space Low (92%)',
                'severity' => 'Warning',
                'status' => 'Acknowledged',
            ],
            [
                'device_name' => 'AP Floor 3',
                'message' => 'Device Unreachable',
                'severity' => 'Critical',
                'status' => 'Open',
            ],
            [
                'device_name' => 'Router-WAN',
                'message' => 'Latency spike',
                'severity' => 'Warning',
                'status' => 'Acknowledged',
            ],
            [
                'device_name' => 'UPS-Main',
                'message' => 'Battery test passed',
                'severity' => 'Warning',
                'status' => 'Acknowledged',
            ],
        ];

        foreach ($alarms as $alarm) {
            Alarm::updateOrCreate(
                [
                    'device_name' => $alarm['device_name'],
                    'message' => $alarm['message']
                ],
                $alarm
            );
        }

        // Seed Sites
        $sites = [
            [
                'name' => 'HQ - Ahmedabad',
                'up_devices' => 45,
                'total_devices' => 48,
                'x_pos' => 35,
                'y_pos' => 45,
            ],
            [
                'name' => 'DC - Mumbai',
                'up_devices' => 34,
                'total_devices' => 36,
                'x_pos' => 30,
                'y_pos' => 55,
            ],
            [
                'name' => 'Branch - Delhi',
                'up_devices' => 17,
                'total_devices' => 18,
                'x_pos' => 38,
                'y_pos' => 30,
            ],
            [
                'name' => 'Branch - Bangalore',
                'up_devices' => 10,
                'total_devices' => 12,
                'x_pos' => 40,
                'y_pos' => 65,
            ],
            [
                'name' => 'ISP POP - Singapore',
                'up_devices' => 6,
                'total_devices' => 6,
                'x_pos' => 65,
                'y_pos' => 50,
            ],
        ];

        foreach ($sites as $site) {
            Site::updateOrCreate(['name' => $site['name']], $site);
        }
    }
}
