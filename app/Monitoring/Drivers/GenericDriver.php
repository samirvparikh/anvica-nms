<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;

class GenericDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address . 'generic');

        return [
            'hostname' => $device->hostname ?? $device->name,
            'cpu' => 12 + ($seed % 30),
            'ram' => 40 + ($seed % 35),
            'disk' => 25 + ($seed % 40),
            'uptime' => ($seed % 30) . ' days',
            'temperature' => 40 + ($seed % 20),
            'interfaces' => [],
            'vpn' => ['status' => 'unknown'],
        ];
    }
}
