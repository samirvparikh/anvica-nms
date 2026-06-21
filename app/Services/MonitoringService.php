<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceInterface;
use App\Models\DeviceInterfaceLog;
use App\Models\DeviceMetric;
use App\Models\DeviceMetricLog;
use App\Models\ServicePoint;
use App\Monitoring\MonitoringDriverFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MonitoringService
{
    public function __construct(
        protected MonitoringDriverFactory $driverFactory,
        protected AlertService $alertService,
        protected DeviceDowntimeService $downtimeService,
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

    /**
     * Store flat request_data keys directly in device_metrics (metric_slug = key).
     *
     * @param  array<string, mixed>  $payload
     */
    public function ingestFlatMetrics(Device $device, array $payload): void
    {
        $recordedAt = Carbon::now();
        $skipKeys = ['interfaces', 'metrics', 'SYSTEM', 'INTERFACE', 'data', 'api_key'];
        $storedMetricKeys = [];

        foreach ($payload as $key => $value) {
            if (in_array($key, $skipKeys, true) || $value === null || $value === '') {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            [$numericValue, $textValue] = $this->parseFlatMetricValue($value);

            $metricData = [
                'metric_value' => $numericValue,
                'metric_text' => $textValue,
                'recorded_at' => $recordedAt,
            ];

            DeviceMetricLog::create([
                'device_id' => $device->id,
                'metric_slug' => (string) $key,
                ...$metricData,
            ]);

            DeviceMetric::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'metric_slug' => (string) $key,
                ],
                $metricData,
            );

            $storedMetricKeys[] = (string) $key;
        }

        $this->syncServicePointsFromMetricKeys($device, $storedMetricKeys);

        $hostname = $payload['Host_Name']
            ?? $payload['host_name']
            ?? $payload['hostname']
            ?? null;

        $pingStatus = $payload['Ping_Status'] ?? $payload['ping_status'] ?? null;
        $previousHealth = $device->health_status;
        $healthStatus = $previousHealth;

        if ($pingStatus !== null && $pingStatus !== '') {
            $pingText = strtoupper(trim((string) $pingStatus));
            $healthStatus = in_array($pingText, ['UP', '1', 'TRUE', 'ONLINE'], true) ? 'Up' : 'Down';
        }

        $device->update([
            'last_seen' => $recordedAt,
            'hostname' => $hostname ?? $device->hostname,
            'health_status' => $healthStatus,
        ]);

        $this->downtimeService->syncFromHealthChange(
            $device->fresh(),
            $previousHealth,
            $healthStatus,
            \App\Models\DeviceDowntimeEvent::SOURCE_PUSH,
        );

        $this->alertService->evaluateDevice($device->fresh(), []);
    }

    /**
     * Store flat metrics plus interfaces[] from /api/device/data.
     *
     * @param  array<string, mixed>  $payload
     * @return array{interfaces_stored: int}
     */
    public function ingestMetricsAndInterfacesData(Device $device, array $payload): array
    {
        $interfaces = $payload['interfaces'] ?? [];
        if (! is_array($interfaces)) {
            $interfaces = [];
        }

        $this->ingestFlatMetrics($device, $payload);

        return [
            'interfaces_stored' => $this->ingestInterfaceRecords($device, $interfaces),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $interfaces
     */
    public function ingestInterfaceRecords(Device $device, array $interfaces): int
    {
        $recordedAt = Carbon::now();
        $count = 0;

        foreach ($interfaces as $iface) {
            if (! is_array($iface)) {
                continue;
            }

            $interfaceName = $iface['if_name']
                ?? $iface['interface_name']
                ?? $iface['name']
                ?? null;

            if (! $interfaceName) {
                continue;
            }

            $status = $this->normalizeInterfaceStatus($iface['status'] ?? '1');
            $rx = (int) ($iface['rx_bytes'] ?? $iface['rx'] ?? 0);
            $tx = (int) ($iface['tx_bytes'] ?? $iface['tx'] ?? 0);
            $rxPackets = (int) ($iface['rx_packets'] ?? 0);
            $txPackets = (int) ($iface['tx_packets'] ?? 0);
            $ifIndex = isset($iface['if_index']) ? (string) $iface['if_index'] : null;

            DeviceInterface::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'interface_name' => $interfaceName,
                ],
                [
                    'status' => $status,
                    'rx' => $rx,
                    'tx' => $tx,
                    'rx_packets' => $rxPackets,
                    'tx_packets' => $txPackets,
                ]
            );

            DeviceInterfaceLog::create([
                'device_id' => $device->id,
                'interface_name' => $interfaceName,
                'if_index' => $ifIndex,
                'status' => $status,
                'rx' => $rx,
                'tx' => $tx,
                'rx_packets' => $rxPackets,
                'tx_packets' => $txPackets,
                'recorded_at' => $recordedAt,
            ]);

            $count++;
        }

        if ($count > 0) {
            $device->update(['last_seen' => $recordedAt]);
        }

        return $count;
    }

    public function normalizeInterfaceStatus(mixed $status): string
    {
        $value = trim((string) $status);

        if ($value === '1' || in_array(strtolower($value), ['up', 'true', 'yes', 'online', 'running'], true)) {
            return 'Up';
        }

        if ($value === '2' || in_array(strtolower($value), ['down', '0', 'false', 'no', 'offline'], true)) {
            return 'Down';
        }

        return $value !== '' ? ucfirst(strtolower($value)) : 'Up';
    }

    /**
     * @return array{0: float, 1: ?string}
     */
    protected function parseFlatMetricValue(mixed $value): array
    {
        if (is_bool($value)) {
            return [$value ? 1 : 0, $value ? 'true' : 'false'];
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return [0, null];
        }

        $upper = strtoupper($stringValue);

        if (in_array($upper, ['UP', 'DOWN', 'ONLINE', 'OFFLINE'], true)) {
            return [
                in_array($upper, ['UP', 'ONLINE'], true) ? 1 : 0,
                $upper,
            ];
        }

        if (is_numeric($stringValue)) {
            return [(float) $stringValue, null];
        }

        return [0, $stringValue];
    }

    /**
     * Create or update service_points on the device service from API metric keys.
     *
     * @param  list<string>  $metricKeys
     */
    protected function syncServicePointsFromMetricKeys(Device $device, array $metricKeys): void
    {
        if (! $device->service_id || $metricKeys === []) {
            return;
        }

        $serviceId = $device->service_id;

        foreach ($metricKeys as $key) {
            if ($this->isDeviceMetaMetricKey($key)) {
                continue;
            }

            $slug = (string) $key;
            $name = $this->metricKeyToPointName($slug);

            $existing = ServicePoint::query()
                ->where('service_id', $serviceId)
                ->where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->orWhere('slug', Str::slug($slug))
                        ->orWhere('slug', strtolower($slug));
                })
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'method' => 'API',
                    'unit' => $this->inferMetricUnit($slug) ?? $existing->unit,
                    'status' => ServicePoint::STATUS_ACTIVE,
                ]);

                continue;
            }

            ServicePoint::create([
                'service_id' => $serviceId,
                'name' => $name,
                'slug' => $slug,
                'method' => 'API',
                'unit' => $this->inferMetricUnit($slug),
                'status' => ServicePoint::STATUS_ACTIVE,
            ]);
        }
    }

    protected function isDeviceMetaMetricKey(string $key): bool
    {
        return in_array($key, [
            'Router',
            'router',
            'Host_Name',
            'host_name',
            'hostname',
            'name',
            'IP_Address',
            'ip_address',
            'target_ip',
            'Target_Ip',
        ], true);
    }

    protected function metricKeyToPointName(string $key): string
    {
        return str_replace('_', ' ', $key);
    }

    protected function inferMetricUnit(string $key): ?string
    {
        $normalized = strtolower($key);

        if (str_contains($normalized, 'temp')) {
            return '°C';
        }

        if (in_array($normalized, ['cpu', 'ram'], true) || str_ends_with($normalized, '_ram')) {
            return '%';
        }

        if (str_contains($normalized, 'ping')) {
            return null;
        }

        if (str_contains($normalized, 'ram_uses') || str_contains($normalized, 'total_ram') || str_contains($normalized, 'bytes')) {
            return 'bytes';
        }

        if (str_contains($normalized, 'time') || str_contains($normalized, 'uptime')) {
            return 'sec';
        }

        return null;
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

        $previousHealth = $device->health_status;
        $newHealth = isset($result['metrics']['ping_status'])
            ? (((float) $result['metrics']['ping_status'] >= 1) ? 'Up' : 'Down')
            : $this->resolveHealthStatus($result['metrics']);

        $device->update([
            'last_seen' => $recordedAt,
            'hostname' => $result['hostname'] ?? $device->hostname,
            'health_status' => $newHealth,
        ]);

        $this->downtimeService->syncFromHealthChange(
            $device->fresh(),
            $previousHealth,
            $newHealth,
            \App\Models\DeviceDowntimeEvent::SOURCE_POLL,
        );

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
