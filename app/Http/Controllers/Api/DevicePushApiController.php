<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ParsesApiPayload;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceInterface;
use App\Models\DeviceMetric;
use App\Monitoring\Normalizers\MetricNormalizer;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevicePushApiController extends Controller
{
    use ParsesApiPayload;

    public function __construct(
        protected MonitoringService $monitoringService,
    ) {}

    /**
     * Device lookup by IP, Router name, or Host_Name — must exist in NMS devices table.
     */
    protected function resolveDevice(Request $request, ?array $payload = null): Device
    {
        $payload ??= $this->extractPayload($request);

        $ip = $request->header('X-Device-Ip')
            ?? $payload['ip_address']
            ?? $payload['IP_Address']
            ?? $request->input('ip_address');

        $device = null;

        if ($ip) {
            $device = Device::where('ip_address', $ip)->first();
        }

        if (! $device) {
            $name = $payload['Router']
                ?? $payload['router']
                ?? $payload['Host_Name']
                ?? $payload['host_name']
                ?? null;

            if ($name) {
                $device = Device::query()
                    ->where('name', $name)
                    ->orWhere('hostname', $name)
                    ->first();
            }
        }

        abort_unless($ip || $device, 422, 'ip_address or Router/Host_Name required (body, query, or X-Device-Ip header).');
        abort_unless($device, 404, 'Device not registered in NMS. Add it under Devices first.');

        $apiKey = $request->header('X-Api-Key') ?? $payload['api_key'] ?? $request->input('api_key');
        if ($apiKey && $device->snmp_community && $apiKey !== $device->snmp_community) {
            abort(401, 'Invalid API key.');
        }

        return $device;
    }

    /**
     * GET /api/device/metrics — read latest | POST|GET — push metrics (flat or nested request_data).
     */
    public function metricsEndpoint(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);

        if ($request->isMethod('GET') && ! MetricNormalizer::isFlatRouterPush($payload) && ! isset($payload['metrics'])) {
            return $this->latestMetrics($request);
        }

        return $this->metrics($request);
    }

    /**
     * POST|GET /api/device/push — full MikroTik flat payload or generic JSON.
     *
     * request_data example:
     * {"ip_address":"192.168.5.1","CPU":"6","Router":"Anvica_Demo","Ram_Uses":"1852352","Total_Ram":"8388608",...}
     */
    public function push(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);
        $this->monitoringService->ingestPush($device, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Device data stored.',
            'device_id' => $device->id,
            'device_name' => $device->name,
            'recorded_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * POST|GET /api/device/metrics — metrics only (flat router params or nested metrics object).
     *
     * Flat request_data:
     * {"ip_address":"192.168.5.1","CPU":"6","CPU_Temp":"60","Ram_Uses":"1852352","Total_Ram":"8388608",...}
     *
     * Nested request_data:
     * {"ip_address":"192.168.5.1","metrics":{"cpu":6,"ram":22,"disk":0,"temperature":60}}
     */
    public function metrics(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        if (MetricNormalizer::isFlatRouterPush($payload)) {
            $info = MetricNormalizer::fromRouterPush($payload);
            $this->monitoringService->ingestPush($device, array_merge($payload, [
                'metrics' => $info['metrics'],
                'hostname' => $info['hostname'],
                'uptime' => $info['uptime'],
            ]));
        } else {
            $validated = validator($payload, [
                'metrics' => 'required|array',
                'metrics.cpu' => 'nullable|numeric',
                'metrics.ram' => 'nullable|numeric',
                'metrics.disk' => 'nullable|numeric',
                'metrics.temperature' => 'nullable|numeric',
                'hostname' => 'nullable|string|max:191',
            ])->validate();

            $this->monitoringService->ingestPush($device, $validated);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Metrics stored.',
            'device_id' => $device->id,
        ]);
    }

    /**
     * POST|GET /api/device/interfaces — push interface traffic data.
     *
     * request_data example:
     * {"ip_address":"192.168.5.1","interfaces":[{"name":"ether1","status":"up","rx":1000,"tx":2000}]}
     */
    public function interfaces(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        $validated = validator($payload, [
            'interfaces' => 'required|array|min:1',
            'interfaces.*.name' => 'required_without:interfaces.*.interface_name|string|max:191',
            'interfaces.*.interface_name' => 'nullable|string|max:191',
            'interfaces.*.status' => 'nullable|string|max:50',
            'interfaces.*.rx' => 'nullable|integer|min:0',
            'interfaces.*.tx' => 'nullable|integer|min:0',
            'interfaces.*.rx_packets' => 'nullable|integer|min:0',
            'interfaces.*.tx_packets' => 'nullable|integer|min:0',
        ])->validate();

        $recordedAt = Carbon::now();

        foreach ($validated['interfaces'] as $iface) {
            DeviceInterface::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'interface_name' => $iface['name'] ?? $iface['interface_name'],
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

        $device->update(['last_seen' => $recordedAt]);

        return response()->json([
            'status' => 'success',
            'message' => 'Interfaces stored.',
            'device_id' => $device->id,
            'count' => count($validated['interfaces']),
        ]);
    }

    /**
     * POST|GET /api/device/heartbeat — online ping + optional hostname.
     *
     * request_data example:
     * {"ip_address":"192.168.5.1","hostname":"Anvica_Demo"}
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        $validated = validator($payload, [
            'hostname' => 'nullable|string|max:191',
            'Host_Name' => 'nullable|string|max:191',
            'host_name' => 'nullable|string|max:191',
        ])->validate();

        $hostname = $validated['hostname']
            ?? $validated['Host_Name']
            ?? $validated['host_name']
            ?? null;

        $device->update([
            'last_seen' => Carbon::now(),
            'hostname' => $hostname ?? $device->hostname,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Heartbeat received.',
            'device_id' => $device->id,
            'last_seen' => $device->fresh()->last_seen?->toIso8601String(),
        ]);
    }

    /**
     * GET /api/device/info — device registration check (router calls NMS).
     */
    public function info(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        return response()->json([
            'device_id' => $device->id,
            'name' => $device->name,
            'hostname' => $device->hostname,
            'ip_address' => $device->ip_address,
            'service' => $device->service?->name,
            'vendor' => $device->vendor?->name,
            'status' => $device->status,
            'health_status' => $device->health_status,
            'last_seen' => $device->last_seen,
        ]);
    }

    /**
     * GET /api/device/metrics — latest stored metrics (device reads own data back).
     */
    public function latestMetrics(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        $metrics = DeviceMetric::where('device_id', $device->id)
            ->latest('recorded_at')
            ->take(20)
            ->get()
            ->groupBy('metric_slug')
            ->map(fn ($group) => $group->first());

        return response()->json([
            'device_id' => $device->id,
            'metrics' => $metrics,
        ]);
    }
}
