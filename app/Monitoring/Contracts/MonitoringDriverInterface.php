<?php

namespace App\Monitoring\Contracts;

use App\Models\Device;

interface MonitoringDriverInterface
{
    public function getSystemInfo(Device $device): array;

    public function getInterfaces(Device $device): array;

    public function getTraffic(Device $device): array;

    public function getVpnStatus(Device $device): array;

    public function getTemperature(Device $device): ?float;

    public function poll(Device $device): array;
}
