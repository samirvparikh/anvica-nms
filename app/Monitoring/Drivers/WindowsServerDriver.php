<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;

class WindowsServerDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address . 'windows');

        return [
            'hostname' => $device->hostname ?? $device->name,
            'cpu' => 22 + ($seed % 45),
            'ram' => 58 + ($seed % 30),
            'disk' => 65 + ($seed % 25),
            'uptime' => ($seed % 45) . ' days',
            'temperature' => 50 + ($seed % 10),
            'interfaces' => [
                [
                    'name' => 'Ethernet0',
                    'status' => 'up',
                    'rx' => 900000,
                    'tx' => 700000,
                    'rx_packets' => 45000,
                    'tx_packets' => 38000,
                ],
            ],
            'vpn' => ['status' => 'n/a'],
        ];
    }
}
