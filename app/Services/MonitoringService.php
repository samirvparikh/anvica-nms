<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceInterface;
use App\Models\DeviceMetric;
use App\Monitoring\MonitoringDriverFactory;
use Carbon\Carbon;

class MonitoringService
{
    public function __construct(
        protected MonitoringDriverFactory $driverFactory,
        protected AlertService $alertService,
    ) {}

    public function poll(Device $device): void
    {
        $driver = $this->driverFactory->make($device);
        $this->storePollResult($device, $driver->poll($device));
    }

    /**
     * Accept push payload from router/device API (MikroTik or generic format).
     */
    public function ingestPush(Device $device, array $payload): void
    {
        if (isset($payload['SYSTEM'])) {
            $info = \App\Monitoring\Normalizers\MetricNormalizer::fromMikroTik($payload);
            $interfaces = $payload['interfaces'] ?? [];
            if (isset($payload['INTERFACE'])) {
                $iface = $payload['INTERFACE'];
                $interfaces = [is_array($iface) && isset($iface['Name']) ? [
                    'name' => $iface['Name'],
                    'status' => ($iface['Running'] ?? false) ? 'up' : 'down',
                    'rx' => $iface['RX'] ?? 0,
                    'tx' => $iface['TX'] ?? 0,
                    'rx_packets' => $iface['RX_Packets'] ?? 0,
                    'tx_packets' => $iface['TX_Packets'] ?? 0,
                ] : $iface];
            }
            $result = [
                'metrics' => [
                    'cpu' => $info['cpu'] ?? 0,
                    'ram' => $info['ram'] ?? 0,
                    'disk' => $info['disk'] ?? 0,
                    'temperature' => $info['temperature'] ?? 0,
                ],
                'interfaces' => $interfaces,
                'hostname' => $info['hostname'] ?? $device->hostname,
                'uptime' => $info['uptime'] ?? null,
            ];
        } else {
            $info = \App\Monitoring\Normalizers\MetricNormalizer::fromGeneric($payload);
            $result = [
                'metrics' => $payload['metrics'] ?? [
                    'cpu' => $info['cpu'] ?? 0,
                    'ram' => $info['ram'] ?? 0,
                    'disk' => $info['disk'] ?? 0,
                    'temperature' => $info['temperature'] ?? 0,
                ],
                'interfaces' => $payl