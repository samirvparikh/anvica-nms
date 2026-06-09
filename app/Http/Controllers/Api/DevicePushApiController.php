<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ParsesApiPayload;
use App\Http\Controllers\Controller;
use App\Models\Device;
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

        $ip = $this->extractIpAddress($request, $payload);
        $name = $this->extractDeviceName($payload);

        $device = null;

        if ($ip) {
            $device = Device::where('ip_address', $ip)->first();
        }

        if (! $device && $name) {
            $device = Device::query()
                ->where('name', $name)
                ->orWhere('hostname', $name)
                ->first();
        }

        abort_unless($ip || $name, 422, 'ip_address or Router/Host_Name required (body, query, or X-Device-Ip header).');
        abort_unless($device, 404, 'Device not registered in NMS. Add it under Devices first.');

        return $this->authorizeDeviceApiKey($request, $payload, $device);
    }

    /**
     * Device lookup for /api/device/metrics — requires both name and ip_address.
     */
    protected function resolveDeviceByNameAndIp(Request $request, ?array $payload = null): Device
    {
        $payload ??= $this->extractPayload($request);

        $ip = $this->extractIpAddress($request, $payload);
        $name = $this->extractDeviceName($payload);

        abort_unless($ip, 422, 'ip_address or target_ip is required (body, query, or X-Device-Ip header).');
        abort_unless($name, 422, 'name is required (use name, Router, or Host_Name in payload).');

        $device = Device::query()
            ->where('ip_address', $ip)
            ->where(function ($query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('hostname', $name);
            })
            ->first();

        abort_unless($device, 404, 'Device not found. No device matches this name and IP address.');

        return $this->authorizeDeviceApiKey($request, $payload, $device);
    }

    protected function extractIpAddress(Request $request, array $payload): ?string
    {
        return $request->header('X-Device-Ip')
            ?? $payload['ip_address']
            ?? $payload['IP_Address']
            ?? $payload['target_ip']
            ?? $payload['Target_Ip']
            ?? $request->input('ip_address');
    }

    protected function extractDeviceName(array $payload): ?string
    {
        return $payload['name']
            ?? $payload['Router']
            ?? $payload['router']
            ?? $payload['Host_Name']
            ?? $payload['host_name']
            ?? null;
    }

    protected function authorizeDeviceApiKey(Request $request, array $payload, Device $device): Device
    {
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
     * POST|GET /api/device/data — combined flat metrics + interfaces[].
     *
     * request_data example:
     * {
     *   "target_ip": "192.168.5.1",
     *   "Router": "Anvica_Demo",
     *   "Host_Name": "Anvica_Demo",
     *   "CPU": "5",
     *   "Ping_Status": "UP",
     *   "interfaces": [
     *     {"if_index":"3","if_name":"ether1","status":"1","rx_bytes":"1000","tx_bytes":"500",...}
     *   ]
     * }
     */
    public function pushMetricsAndInterfacesData(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDeviceByNameAndIp($request, $payload);

        validator($payload, [
            'interfaces' => 'nullable|array',
            'interfaces.*.if_name' => 'required_without:interfaces.*.interface_name,interfaces.*.name|string|max:191',
            'interfaces.*.interface_name' => 'nullable|string|max:191',
            'interfaces.*.name' => 'nullable|string|max:191',
            'interfaces.*.if_index' => 'nullable|string|max:50',
            'interfaces.*.status' => 'nullable|string|max:50',
            'interfaces.*.rx_bytes' => 'nullable|numeric|min:0',
            'interfaces.*.tx_bytes' => 'nullable|numeric|min:0',
            'interfaces.*.rx' => 'nullable|numeric|min:0',
            'interfaces.*.tx' => 'nullable|numeric|min:0',
            'interfaces.*.rx_packets' => 'nullable|numeric|min:0',
            'interfaces.*.tx_packets' => 'nullable|numeric|min:0',
        ])->validate();

        $result = $this->monitoringService->ingestMetricsAndInterfacesData($device, $payload);
        $recordedAt = Carbon::now();

        return response()->json([
            'status' => 'success',
            'message' => 'Device metrics and interface data stored.',
            'device_id' => $device->id,
            'device_name' => $device->name,
            'interfaces_stored' => $result['interfaces_stored'],
            'recorded_at' => $recordedAt->toIso8601String(),
        ]);
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
     * {"name":"Anvica_Demo","ip_address":"192.168.5.1","CPU":"6","CPU_Temp":"60","Ram_Uses":"1852352","Total_Ram":"8388608",...}
     *
     * Nested request_data:
     * {"name":"Anvica_Demo","ip_address":"192.168.5.1","metrics":{"cpu":6,"ram":22,"disk":0,"temperature":60}}
     */
    public function metrics(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDeviceByNameAndIp($request, $payload);

        if (isset($payload['metrics']) && is_array($payload['metrics'])
            && ! isset($payload['Router']) && ! isset($payload['IP_Address']) && ! isset($payload['target_ip'])) {
            $validated = validator($payload, [
                'metrics' => 'required|array',
                'metrics.cpu' => 'nullable|numeric',
                'metrics.ram' => 'nullable|numeric',
                'metrics.disk' => 'nullable|numeric',
                'metrics.temperature' => 'nullable|numeric',
                'hostname' => 'nullable|string|max:191',
            ])->validate();

            $this->monitoringService->ingestPush($device, $validated);
        } else {
            $this->monitoringService->ingestFlatMetrics($device, $payload);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Metrics stored.',
            'device_id' => $device->id,
        ]);
    }

    
    /**
     * POST|GET /api/device/interfaces/data — flat MikroTik interface push (one interface per request).
     *
     * request_data example:
     * {
     *   "Router": "Anvica_Demo",
     *   "Host_Name": "Anvica_Demo",
     *   "target_ip": "192.168.5.1",
     *   "if_name": "ether1",
     *   "if_index": "3",
     *   "status": "1",
     *   "rx_bytes": "402554371",
     *   "tx_bytes": "652943407",
     *   "rx_packets": "2861513",
     *   "tx_packets": "5271135"
     * }
     */
    public function interfaces(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDeviceByNameAndIp($request, $payload);

        $validated = validator($payload, [
            'if_name' => 'required_without:interface_name|string|max:191',
            'interface_name' => 'nullable|string|max:191',
            'if_index' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'rx_bytes' => 'nullable|numeric|min:0',
            'tx_bytes' => 'nullable|numeric|min:0',
            'rx_packets' => 'nullable|numeric|min:0',
            'tx_packets' => 'nullable|numeric|min:0',
        ])->validate();

        $interfaceName = $validated['if_name'] ?? $validated['interface_name'];
        $this->monitoringService->ingestInterfaceRecords($device, [[
            'if_name' => $interfaceName,
            'if_index' => $validated['if_index'] ?? null,
            'status' => $validated['status'] ?? '1',
            'rx_bytes' => $validated['rx_bytes'] ?? 0,
            'tx_bytes' => $validated['tx_bytes'] ?? 0,
            'rx_packets' => $validated['rx_packets'] ?? 0,
            'tx_packets' => $validated['tx_packets'] ?? 0,
        ]]);

        return response()->json([
            'status' => 'success',
            'message' => 'Interface data stored.',
            'device_id' => $device->id,
            'device_name' => $device->name,
            'interface_name' => $interfaceName,
            'if_index' => $validated['if_index'] ?? null,
            'recorded_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    public function interfacesData(Request $request): JsonResponse
    {
        $payload = $this->extractPayload($request);
        $device = $this->resolveDevice($request, $payload);

        $validated = validator($payload, [
            'interfaces' => 'required|array|min:1',
            'interfaces.*.name' => 'required_without:interfaces.*.interface_name,interfaces.*.if_name|string|max:191',
            'interfaces.*.interface_name' => 'nullable|string|max:191',
            'interfaces.*.if_name' => 'nullable|string|max:191',
            'interfaces.*.status' => 'nullable|string|max:50',
            'interfaces.*.rx' => 'nullable|integer|min:0',
            'interfaces.*.tx' => 'nullable|integer|min:0',
            'interfaces.*.rx_bytes' => 'nullable|integer|min:0',
            'interfaces.*.tx_bytes' => 'nullable|integer|min:0',
            'interfaces.*.rx_packets' => 'nullable|integer|min:0',
            'interfaces.*.tx_packets' => 'nullable|integer|min:0',
        ])->validate();

        $count = $this->monitoringService->ingestInterfaceRecords($device, $validated['interfaces']);

        return response()->json([
            'status' => 'success',
            'message' => 'Interfaces stored.',
            'device_id' => $device->id,
            'count' => $count,
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
        $device = $this->resolveDeviceByNameAndIp($request, $payload);

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
