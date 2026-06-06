<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;

class LinuxServerDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address . 'linux');

        return [
            'hostname' => $device->hostname ?? $device->name,
            'cpu' => 15 + ($seed % 50),
            'ram' => 42 + ($seed % 40),
            'disk' => 55 + ($seed % 30),
            'uptime' => ($seed % 120) . ' days',
            'temperature' => 44 + ($seed % 12),
            'interfaces' => [
                [
                    'name' => 'eth0',
                    'status' => 'up',
                    'rx' => 1500000,
                    'tx' => 1200000,
                    'rx_packets' => 65000,
                    'tx_packets' => 54000,
                ],
            ],
            'vpn' => ['status' => 'n/a'],
        ];
    }
}
