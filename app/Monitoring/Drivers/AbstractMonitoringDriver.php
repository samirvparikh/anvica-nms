<?php

namespace App\Monitoring\Drivers;

use App\Models\Device;
use App\Monitoring\Contracts\MonitoringDriverInterface;
use App\Monitoring\Normalizers\MetricNormalizer;

abstract class AbstractMonitoringDriver implements MonitoringDriverInterface
{
    abstract protected function fetchRaw(Device $device): array;

    public function getSystemInfo(Device $device): array
    {
        return MetricNormalizer::fromGeneric($this->fetchRaw($device));
    }

    public function getInterfaces(Device $device): array
    {
        $raw = $this->fetchRaw($device);

        return $raw['interfaces'] ?? [];
    }

    public function getTraffic(Device $device): array
    {
        return collect($this->getInterfaces($device))
            ->map(fn (array $iface) => [
                'name' => $iface['name'] ?? $iface['interface_name'] ?? 'unknown',
                'rx' => (int) ($iface['rx'] ?? 0),
                'tx' => (int) ($iface['tx'] ?? 0),
            ])
            ->all();
    }

    public function getVpnStatus(Device $device): array
    {
        return $this->fetchRaw($device)['vpn'] ?? ['status' => 'unknown'];
    }

    public function getTemperature(Device $device): ?float
    {
        $info = $this->getSystemInfo($device);

        return $info['temperature'] ?? null;
    }

    public function poll(Device $device): array
    {
        $info = $this->getSystemInfo($device);

        return [
            'metrics' => [
                'cpu' => $info['cpu'] ?? 0,
                'ram' => $info['ram'] ?? 0,
                'disk' => $info['disk'] ?? 0,
                'temperature' => $info['temperature'] ?? 0,
            ],
            'interfaces' => $this->getInterfaces($device),
            'vpn' => $this->getVpnStatus($device),
            'hostname' => $info['hostname'] ?? $device->hostname ?? $device->name,
            'uptime' => $info['uptime'] ?? null,
        ];
    }
}
