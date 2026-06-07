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
                    'rx_packets' => $iface['RX_Packets'] ?? $iface['RX_Packet'] ?? 0,
                    'tx_packets' => $iface['TX_Packets'] ?? $iface['TX_Packet'] ?? 0,
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
        } elseif (\App\Monitoring\Normalizers\MetricNormalizer::isFlatRouterPush($payload)) {
            $info = \App\Monitoring\Normalizers\MetricNormalizer::fromRouterPush($payload);
            $result = [
                'metrics' => $info['metrics'],
                'interfaces' => $payload['interfaces'] ?? [],
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
                'interfaces' => $payload['interfaces'] ?? [],
                'hostname' => $info['hostname'] ?? $device->hostname,
                'uptime' => $info['uptime'] ?? null,
            ];
        }

        $this->storePollResult($device, $result);
    }

    public function storePollResult(Device $device, array $result): void
    {
        $recordedAt = Carbon::now();

        foreach ($result['metrics'] as $slug => $value) {
            DeviceMetric::create([
                'device_id' => $device->id,
                'metric_slug' => $slug,
                'metric_value' => $value,
                'recorded_at' => $recordedAt,
            ]);
        }

        foreach ($result['interfaces'] as $iface) {
            DeviceInterface::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'interface_name' => $iface['name'] ?? $iface['interface_name'] ?? 'unknown',
                ],
                [
                    'status' => $iface['status'] ?? 'up',
                    'rx' => (int) ($iface['rx'] ?? 0),
                    'tx' => (int) ($iface['tx'] ?? 0),
                    'rx_packets' => (int) ($iface['rx_packets'] ?? 0),
                    'tx_packets' => (int) ($iface['tx_packets'] ?? 0),
                ]
            );
        }

        $device->update([
            'last_seen' => $recordedAt,
            'hostname' => $result['hostname'] ?? $device->hostname,
            'health_status' => $this->resolveHealthStatus($result['metrics']),
        ]);

        $this->alertService->evaluateDevice($device->fresh(), $result['metrics']);
    }

    /**
     * @param  array<string, float|int>  $metrics
     */
    protected function resolveHealthStatus(array $metrics): string
    {
        $cpu = (float) ($metrics['cpu'] ?? 0);
        $ram = (float) ($metrics['ram'] ?? 0);
        $disk = (float) ($metrics['disk'] ?? 0);
        $temp = (float) ($metrics['temperature'] ?? 0);

        if ($cpu >= 95 || $ram >= 95 || $disk >= 95 || $temp >= 85) {
            return 'Down';
        }

        if ($cpu >= 80 || $ram >= 90 || $disk >= 90 || $temp >= 70) {
            return 'Warning';
        }

        return 'Up';
    }
}
