<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Models\Device;
use App\Models\DeviceMetric;
use App\Monitoring\Normalizers\MikroTikPipeParser;
use App\Services\ApiRequestLogger;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
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

        $payload = $this->extractRouterPayload($request);
        // $routerName = $payload['SYSTEM']['Router'] ?? null;
        $routerName = $payload['Router'] ?? null;

        if (! $routerName) {
            return response()->json([
                'status' => 'error',
                'message' => 'Router name not found in payload. Expected SYSTEM.Router or Router:_Name in pipe format.',
            ], 422);
        }

        $device = Device::query()
            ->where('name', $routerName)
            ->orWhere('hostname', $routerName)
            ->first();

        if (! $device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found. Create a device with name or hostname: ' . $routerName,
                'router_name' => $routerName,
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

    protected function extractRouterPayload(Request $request): array
    {
        $all = $request->all();

        if (isset($all['SYSTEM'])) {
            return $all;
        }

        if ($request->filled('data') && is_string($request->input('data'))) {
            return MikroTikPipeParser::parse($request->input('data'));
        }

        if (count($all) === 1) {
            $key = array_key_first($all);
            if (is_string($key) && str_contains($key, '_|_')) {
                return MikroTikPipeParser::parse($key);
            }
        }

        $body = trim($request->getContent());
        if ($body !== '' && str_contains($body, '_|_')) {
            return MikroTikPipeParser::parse($body);
        }

        return $all;
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
