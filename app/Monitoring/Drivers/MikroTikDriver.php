<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;
use App\Monitoring\Normalizers\MetricNormalizer;

class MikroTikDriver extends AbstractMonitoringDriver
{
    protected function fetchRaw(Device $device): array
    {
        $seed = crc32($device->ip_address);
        $cpu = ($seed % 35) + 5;
        $ramUsed = 2_700_804_096 + ($seed % 500_000_000);
        $ramTotal = 8_589_934_592;
        $diskUsed = 381_620_224 + ($seed % 100_000_000);
        $diskTotal = 1_073_741_824;

        return [
            'SYSTEM' => [
                'Router' => $device->hostname ?? $device->name,
                'CPU' => $cpu . '%',
                'RAM_Used' => $ramUsed . '/' . $ramTotal,
                'Disk_Used' => $diskUsed . '/' . $diskTotal,
                'Uptime' => ($seed % 14) . 'd' . ($seed % 24) . ':12:08',
                'Temperature' => 38 + ($seed % 18),
            ],
            'interfaces' => [
                [
                    'name' => 'ether1',
                    'status' => 'up',
                    'rx' => 1200000 + ($seed % 900000),
                    'tx' => 980000 + ($seed % 700000),
                    'rx_packets' => 45000,
                    'tx_packets' => 39000,
                ],
                [
                    'name' => 'ether7',
                    'status' => 'down',
                    'rx' => 0,
                    'tx' => 0,
                    'rx_packets' => 0,
                    'tx_packets' => 0,
                ],
            ],
            'vpn' => ['status' => $cpu > 30 ? 'connected' : 'disconnected'],
        ];
    }

    public function getSystemInfo(Device $device): array
    {
        return MetricNormalizer::fromMikroTik($this->fetchRaw($device));
    }
}
