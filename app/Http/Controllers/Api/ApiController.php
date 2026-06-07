<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ParsesApiPayload;
use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Models\Device;
use App\Models\DeviceMetric;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ParsesApiPayload;

    public function __construct(
        protected MonitoringService $monitoringService,
    ) {}

    /**
     * Handle incoming test request and log details to database.
     */
    public function handleTestRequest(Request $request): JsonResponse
    {
        $log = $this->logRequest($request);

        return response()->json([
            'status' => 'success',
            'message' => 'API request successfully logged to MySQL database.',
            'logged_data' => [
                'id' => $log->id,
                'url' => $log->url,
                'method' => $log->method,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'referer' => $log->referer,
                'request_data' => $log->request_data,
                'timestamp' => $log->created_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * MikroTik router push — parse payload, find device by name, store metrics.
     *
     * POST /api/router
     * Accepts pipe format, JSON, or form body from MikroTik script.
     */
    public function router(Request $request): JsonResponse
    {
        $this->logRequest($request);

        $payload = $this->extractPayload($request);
        $routerName = $payload['Router']
            ?? $payload['router']
            ?? $payload['Host_Name']
            ?? $payload['host_name']
            ?? $payload['SYSTEM']['Router']
            ?? null;

        if (! $routerName && empty($payload['IP_Address']) && empty($payload['ip_address'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Router name not found in payload. Expected Router, Host_Name, or IP_Address.',
            ], 422);
        }

        $device = null;

        if ($routerName) {
            $device = Device::query()
                ->where('name', $routerName)
                ->orWhere('hostname', $routerName)
                ->first();
        }

        $ipAddress = $payload['IP_Address'] ?? $payload['ip_address'] ?? null;

        if (! $device && $ipAddress) {
            $device = Device::query()->where('ip_address', $ipAddress)->first();
        }

        if (! $device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found. Create a device matching Router/Host_Name or IP_Address.',
                'router_name' => $routerName,
                'ip_address' => $ipAddress,
            ], 404);
        }

        $this->monitoringService->ingestPush($device, $payload);

        $device->refresh();

        $metrics = DeviceMetric::where('device_id', $device->id)
            ->where('recorded_at', '>=', Carbon::now()->subMinute())
            ->get()
            ->map(fn ($m) => [
                'metric_slug' => $m->metric_slug,
                'metric_value' => $m->metric_value,
                'recorded_at' => $m->recorded_at->toIso8601String(),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Router data stored in device_metrics.',
            'device_id' => $device->id,
            'device_name' => $device->name,
            'router_name' => $routerName,
            'metrics_stored' => $metrics,
            'device_status' => $device->status,
            'last_seen' => $device->last_seen?->toIso8601String(),
        ], 200);
    }

    protected function logRequest(Request $request): ApiRequestLog
    {
        return ApiRequestLog::create([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer') ?? $request->header('referrer') ?? $request->server('HTTP_REFERER'),
            'request_data' => $request->all() ?: ['raw' => $request->getContent()],
            'headers' => collect($request->headers->all())->map(function ($item) {
                return is_array($item) && count($item) === 1 ? $item[0] : $item;
            })->toArray(),
        ]);
    }
}
