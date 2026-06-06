<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;

class CiscoDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address . 'cisco');

        return [
            'hostname' => $device->hostname ?? $device->name,
            'cpu' => 10 + ($seed % 55),
            'ram' => 35 + ($seed % 50),
            'disk' => 20 + ($seed % 60),
            'uptime' => ($seed % 90) . ' days',
            'temperature' => 42 + ($seed % 20),
            'interfaces' => [
                [
                    'name' => 'GigabitEthernet0/1',
                    'status' => 'up',
                    'rx' => 2500000,
                    'tx' => 1800000,
                    'rx_packets' => 88000,
                    'tx_packets' => 72000,
                ],
            ],
            'vpn' => ['status' => 'connected'],
        ];
    }
}
