<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceVendor;
use App\Models\Service;
use App\Models\ServicePoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'Router' => [
                'icon' => 'router',
                'points' => [
                    ['CPU', 'CPU', 'API', '%', 80, 95],
                    ['Ram Used', 'Ram_Used', 'API', 'bytes', null, null],
                    ['Total Ram', 'Total_Ram', 'API', 'bytes', null, null],
                    ['CPU Temp', 'CPU_Temp', 'API', '°C', 70, 85],
                    ['MB Temp', 'MB_Temp', 'API', '°C', 70, 85],
                    ['Board Temp', 'Board_Temp', 'API', '°C', 70, 85],
                    ['UP Time', 'UP_Time', 'API', 'sec', null, null],
                    ['Ping Status', 'Ping_Status', 'API', null, null, null],
                    ['Power1 Status', 'Power1_Status', 'API', null, null, null],
                    ['Power2 Status', 'Power2_Status', 'API', null, null, null],
                ],
                'vendors' => ['MikroTik', 'Cisco', 'Juniper', 'Huawei', 'Ubiquiti'],
            ],
            'Switch' => [
                'icon' => 'switch',
                'points' => [
                    ['Port Up/Down', 'port-status', 'SNMP', null, null, null],
                    ['RX/TX Traffic', 'traffic', 'SNMP', 'bps', null, null],
                    ['CPU', 'cpu', 'SNMP', '%', 80, 95],
                    ['MAC Count', 'mac-count', 'SNMP', 'count', null, null],
                    ['PoE Status', 'poe-status', 'SNMP', null, null, null],
                    ['Temperature', 'temperature', 'SNMP', 'C', 70, 85],
                    ['Error Packets', 'error-packets', 'SNMP', 'count', null, null],
                ],
                'vendors' => ['Cisco', 'MikroTik', 'Ubiquiti', 'D-Link', 'HP'],
            ],
            'Firewall' => [
                'icon' => 'firewall',
                'points' => [
                    ['CPU', 'cpu', 'SNMP', '%', 80, 95],
                    ['Sessions', 'sessions', 'API', 'count', null, null],
                    ['VPN Tunnel', 'vpn-tunnel', 'API', null, null, null],
                    ['WAN Health', 'wan-health', 'SNMP', null, null, null],
                    ['Threat Logs', 'threat-logs', 'Syslog', null, null, null],
                    ['Interface Usage', 'traffic', 'SNMP', 'bps', null, null],
                ],
                'vendors' => ['Fortigate', 'Sophos', 'SonicWall', 'Palo Alto', 'Cisco'],
            ],
            'Access Point' => [
                'icon' => 'access-point',
                'points' => [
                    ['Client Count', 'client-count', 'SNMP', 'count', null, null],
                    ['Signal Strength', 'signal-strength', 'SNMP', 'dBm', null, null],
                    ['Channel Usage', 'channel-usage', 'SNMP', '%', null, null],
                    ['Throughput', 'throughput', 'SNMP', 'bps', null, null],
                    ['AP Status', 'ap-status', 'Ping/SNMP', null, null, null],
                ],
                'vendors' => ['Ubiquiti', 'TP-Link', 'Cisco', 'Aruba', 'MikroTik'],
            ],
            'Server' => [
                'icon' => 'server',
                'points' => [
                    ['CPU', 'cpu', 'SNMP/WMI', '%', 80, 95],
                    ['RAM', 'ram', 'SNMP/WMI', '%', 90, 95],
                    ['Disk Usage', 'disk', 'SNMP/WMI', '%', 90, 95],
                    ['Service Status', 'service-status', 'WMI', null, null, null],
                    ['Temperature', 'temperature', 'SNMP', 'C', 70, 85],
                    ['Network Usage', 'traffic', 'SNMP', 'bps', null, null],
                ],
                'vendors' => ['Windows Server', 'Linux Server'],
            ],
            'CCTV' => [
                'icon' => 'cctv',
                'points' => [
                    ['Camera Online', 'camera-online', 'Ping', null, null, null],
                    ['HDD Status', 'hdd-status', 'SNMP', null, null, null],
                    ['Recording Status', 'recording-status', 'API', null, null, null],
                    ['Bitrate', 'bitrate', 'SNMP', 'bps', null, null],
                    ['Camera Stream', 'camera-stream', 'RTSP/API', null, null, null],
                ],
                'vendors' => ['Hikvision', 'Dahua', 'CP Plus'],
            ],
            'UPS' => [
                'icon' => 'ups',
                'points' => [
                    ['Battery %', 'battery', 'SNMP', '%', 20, 10],
                    ['Load %', 'load', 'SNMP', '%', 80, 95],
                    ['Input Voltage', 'input-voltage', 'SNMP', 'V', null, null],
                    ['Output Voltage', 'output-voltage', 'SNMP', 'V', null, null],
                    ['Battery Runtime', 'battery-runtime', 'SNMP', 'min', null, null],
                    ['Temperature', 'temperature', 'SNMP', 'C', 70, 85],
                ],
                'vendors' => ['APC', 'Numeric', 'Vertiv'],
            ],
        ];

        foreach ($services as $serviceName => $config) {
            $service = Service::updateOrCreate(
                ['slug' => Str::slug($serviceName)],
                [
                    'name' => $serviceName,
                    'icon' => $config['icon'],
                    'status' => Service::STATUS_ACTIVE,
                ]
            );

            $service->points()->delete();

            foreach ($config['points'] as $point) {
                $service->points()->create([
                    'name' => $point[0],
                    'slug' => $point[1],
                    'method' => $point[2],
                    'unit' => $point[3],
                    'warning_threshold' => $point[4],
                    'critical_threshold' => $point[5],
                    'status' => ServicePoint::STATUS_ACTIVE,
                ]);
            }

            foreach ($config['vendors'] as $vendorName) {
                DeviceVendor::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'slug' => Str::slug($vendorName),
                    ],
                    [
                        'name' => $vendorName,
                        'status' => DeviceVendor::STATUS_ACTIVE,
                    ]
                );
            }
        }

        $deviceMap = [
            'Core-Switch01' => ['service' => 'switch', 'vendor' => 'cisco'],
            'Firewall-01' => ['service' => 'firewall', 'vendor' => 'fortigate'],
            'Router-WAN' => ['service' => 'router', 'vendor' => 'mikrotik'],
            'AP-Floor3' => ['service' => 'access-point', 'vendor' => 'ubiquiti'],
            'Server-02' => ['service' => 'server', 'vendor' => 'linux-server'],
            'CCTV-Lobby' => ['service' => 'cctv', 'vendor' => 'hikvision'],
            'UPS-Main' => ['service' => 'ups', 'vendor' => 'apc'],
            'Anvica_Demo' => ['service' => 'router', 'vendor' => 'mikrotik'],
        ];

        $samir = \App\Models\User::where('email', 'samir@gmail.com')->first();
        $vijay = \App\Models\User::where('email', 'vijay@gmail.com')->first();
        $userAssignments = [
            // 'Anvica_Demo' => $samir?->id,
        ];

        foreach ($deviceMap as $deviceName => $map) {
            $service = Service::where('slug', $map['service'])->first();
            $vendor = $service
                ? DeviceVendor::where('service_id', $service->id)->where('slug', $map['vendor'])->first()
                : null;

            Device::where('asset_name', $deviceName)->update([
                // 'user_id' => $userAssignments[$deviceName] ?? null,
                'service_id' => 1,
                'vendor_id' => 1,
                // 'device_type' => $service?->name,
                // 'hostname' => $deviceName,
                'snmp_version' => '2c',
                'snmp_port' => 161,
                // 'snmp_community' => 'Anvica_NMS',
            ]);
        }

        $firewall = Device::where('asset_name', 'Firewall-01')->first();
        if ($firewall && ! Alert::where('device_id', $firewall->id)->exists()) {
            $cpuPoint = ServicePoint::where('slug', 'cpu')->whereHas('service', fn ($q) => $q->where('slug', 'firewall'))->first();
            Alert::create([
                'device_id' => $firewall->id,
                'service_point_id' => $cpuPoint?->id,
                'severity' => Alert::SEVERITY_CRITICAL,
                'message' => 'CPU usage exceeded threshold on Firewall-01: 95.00%',
                'status' => Alert::STATUS_OPEN,
            ]);
        }
    }
}
