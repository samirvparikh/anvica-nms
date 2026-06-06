<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;

class FortigateDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address . 'fortigate');

        return [
            'hostname' => $device->hostname ?? $device->name,
            'cpu' => 18 + ($seed % 40),
            'ram' => 48 + ($seed % 35),
            'disk' => 30 + ($seed % 25),
            'uptime' => ($seed % 60) . ' days',
            'temperature' => 45 + ($seed % 15),
            'interfaces' => [
                [
                    'name' => 'port1',
                    'status' => 'up',
                    'rx' => 3200000,
                    'tx' => 2900000,
                    'rx_packets' => 120000,
                    'tx_packets' => 110000,
                ],
            ],
            'vpn' => [
                'status' => 'connected',
                'tunnels' => 3 + ($seed % 4),
                'sessions' => 1200 + ($seed % 800),
            ],
        ];
    }
}
