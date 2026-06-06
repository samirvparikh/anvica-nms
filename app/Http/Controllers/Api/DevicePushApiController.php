<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceInterface;
use App\Models\DeviceMetric;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevicePushApiController extends Controller
{
    public function __construct(
        protected MonitoringService $monitoringService,
    ) {}

    /**
     * Device lookup by IP — must exist in NMS devices table.
     */
    protected function resolveDevice(Request $request): Device
    {
        $ip = $request->header('X-Device-Ip') ?? $request->input('ip_address');

        abort_unless($ip, 422, 'ip_address required (body or X-Device-Ip header).');

        $device = Device::where('ip_address', $ip)->first();

        abort_unless($device, 404, 'Device not registered in NMS. Add it under Devices first.');

        $apiKey = $request->header('X-Api-Key') ?? $request->input('api_key');
        if ($apiKey && $device->snmp_community && $apiKey !== $device->snmp_community) {
            abort(401, 'Invalid API key.');
        }

        return $device;
    }

    /**
     * POST /api/device/push — full MikroTik or generic payload.
     */
    public function push(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        $this->monitoringService->ingestPush($device, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Device data stored.',
            'device_id' => $device->id,
            'device_name' => $device->name,
            'recorded_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * POST /api/device/metrics — push CPU, RAM, disk, temperature only.
     */
    public function metrics(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        $validated = $request->validate([
            'metrics' => 'required|array',
            'metrics.cpu' => 'nullable|numeric',
            'metrics.ram' => 'nullable|numeric',
            'metrics.disk' => 'nullable|numeric',
            'metrics.temperature' => 'nullable|numeric',
            'hostname' => 'nullable|string|max:191',
        ]);

        $this->monitoringService->ingestPush($device, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Metrics stored.',
            'device_id' => $device->id,
        ]);
    }

    /**
     * POST /api/device/interfaces — push interface traffic data.
     */
    public function interfaces(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        $validated = $request->validate([
            'interfaces' => 'required|array|min:1',
            'interfaces.*.name' => 'required_without:interfaces.*.interface_name|string|max:191',
            'interfaces.*.interface_name' => 'nullable|string|max:191',
            'interfaces.*.status' => 'nullable|string|max:50',
            'interfaces.*.rx' => 'nullable|integer|min:0',
            'interfaces.*.tx' => 'nullable|integer|min:0',
            'interfaces.*.rx_packets' => 'nullable|integer|min:0',
            'interfaces.*.tx_packets' => 'nullable|integer|min:0',
        ]);

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
     * POST /api/device/heartbeat — online ping + optional status.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        $validated = $request->validate([
            'status' => 'nullable|in:Up,Warning,Down',
            'hostname' => 'nullable|string|max:191',
        ]);

        $device->update([
            'last_seen' => Carbon::now(),
            'status' => $validated['status'] ?? $device->status,
            'hostname' => $validated['hostname'] ?? $device->hostname,
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
        $device = $this->resolveDevice($request);

        return response()->json([
            'device_id' => $device->id,
            'name' => $device->name,
            'hostname' => $device->hostname,
            'ip_address' => $device->ip_address,
            'service' => $device->service?->name,
            'vendor' => $device->vendor?->name,
            'status' => $device->status,
            'last_seen' => $device->last_seen,
        ]);
    }

    /**
     * GET /api/device/metrics — latest stored metrics (device reads own data back).
     */
    public function latestMetrics(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

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
