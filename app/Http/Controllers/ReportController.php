<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use App\Support\ByteFormatter;
use App\Services\FaultManagementReportService;
use App\Services\UserScopeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function index()
    {
        return view('reports.index');
    }

    public function deviceManagement(Request $request)
    {
        $user = $request->user();
        $isAdmin = (bool) $user->is_admin;
        $customerId = null;
        $selectedCustomer = null;
        $customers = collect();

        if ($isAdmin) {
            $customerId = $request->integer('user_id') ?: null;

            if ($customerId) {
                $selectedCustomer = User::query()
                    ->where('is_admin', false)
                    ->where('role', User::ROLE_USER)
                    ->find($customerId);

                if (! $selectedCustomer) {
                    $customerId = null;
                }
            }

            $customers = User::query()
                ->where('is_admin', false)
                ->where('role', User::ROLE_USER)
                ->orderBy('name')
                ->get();
        }

        $devices = $this->userScope
            ->devicesQuery($user, $isAdmin ? $customerId : null)
            ->with(['user', 'service', 'vendor'])
            ->orderBy('name')
            ->get();

        $scopedInterfaces = $this->userScope
            ->interfacesQuery($user, $isAdmin ? $customerId : null)
            ->orderBy('interface_name')
            ->get()
            ->groupBy('device_id');

        $latestMetrics = $this->userScope->latestMetricsByDevice($user, $isAdmin ? $customerId : null);
        $deviceHealth = $this->userScope->deviceHealthByRecentMetrics($user, $isAdmin ? $customerId : null);

        return view('reports.device-management', compact(
            'devices',
            'scopedInterfaces',
            'latestMetrics',
            'deviceHealth',
            'customers',
            'customerId',
            'selectedCustomer',
            'isAdmin',
        ));
    }

    public function faultManagement(Request $request)
    {
        $filters = $this->resolveReportFilters($request);

        return view('reports.fault-management', $filters);
    }

    public function faultManagementData(Request $request, FaultManagementReportService $reportService): JsonResponse
    {
        $filters = $this->resolveReportFilters($request);

        return response()->json(
            $reportService->build(
                $request->user(),
                $filters['customerId'],
                $filters['from'],
                $filters['to'],
            )
        );
    }

    public function performanceTraffic()
    {
        return view('reports.performance-traffic');
    }

    public function inventorySla()
    {
        return view('reports.inventory-sla');
    }

    public function slaTicketing()
    {
        return view('reports.sla-ticketing');
    }

    public function show(Request $request, Device $device)
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $device->load(['user', 'service', 'vendor']);
        $logs = $this->getDeviceReportLogs($request, $device, $context);

        return view('reports.show', [
            'device' => $device,
            'logs' => $logs,
            'customerId' => $context['customerId'],
            'isAdmin' => $context['isAdmin'],
        ]);
    }

    public function deviceLogs(Request $request, Device $device): JsonResponse
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $logs = $this->getDeviceReportLogs($request, $device, $context);

        return response()->json([
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
            ],
            'logs' => $logs->map(fn ($log) => $this->formatLogRow($log)),
        ]);
    }

    public function interfaceLogs(Request $request, Device $device): JsonResponse
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $interfaceName = trim((string) $request->query('interface_name', ''));

        abort_unless($interfaceName !== '', 422, 'interface_name is required.');

        $logs = $this->getDeviceInterfaceLogs($request, $device, $context, $interfaceName);

        return response()->json([
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
            ],
            'interface_name' => $interfaceName,
            'logs' => $logs->map(fn ($log) => $this->formatInterfaceLogRow($log)),
        ]);
    }

    public function showInterfaceLog(Request $request, Device $device)
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $interfaceName = trim((string) $request->query('interface_name', ''));

        abort_unless($interfaceName !== '', 422, 'interface_name is required.');

        $device->load(['user', 'service', 'vendor']);
        $logs = $this->getDeviceInterfaceLogs($request, $device, $context, $interfaceName);

        return view('reports.interface-log', [
            'device' => $device,
            'interfaceName' => $interfaceName,
            'logs' => $logs,
            'customerId' => $context['customerId'],
            'isAdmin' => $context['isAdmin'],
        ]);
    }

    public function exportExcel(Request $request, Device $device): StreamedResponse
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $logs = $this->getDeviceReportLogs($request, $device, $context);
        $filename = $this->reportFilename($device, 'csv');

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Recorded At', 'Metric', 'Value', 'Text']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->recorded_at->format('Y-m-d H:i:s'),
                    $log->metric_slug,
                    $log->metric_value,
                    $log->metric_text ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request, Device $device)
    {
        $context = $this->resolveDeviceReportContext($request, $device);
        $device->load(['user', 'service', 'vendor']);
        $logs = $this->getDeviceReportLogs($request, $device, $context);

        $pdf = Pdf::loadView('reports.pdf', [
            'device' => $device,
            'logs' => $logs,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename($device, 'pdf'));
    }

    /**
     * @return array{isAdmin: bool, customerId: ?int}
     */
    protected function resolveDeviceReportContext(Request $request, Device $device): array
    {
        $user = $request->user();
        $isAdmin = (bool) $user->is_admin;

        if (! $this->userScope->canAccessDevice($user, $device)) {
            abort(403);
        }

        $customerId = $isAdmin ? ($request->integer('user_id') ?: null) : null;

        if ($isAdmin && $customerId && $device->user_id !== $customerId) {
            abort(403);
        }

        return [
            'isAdmin' => $isAdmin,
            'customerId' => $customerId,
        ];
    }

    /**
     * @param  array{isAdmin: bool, customerId: ?int}  $context
     */
    protected function getDeviceReportLogs(Request $request, Device $device, array $context)
    {
        return $this->userScope
            ->metricLogsQuery($request->user(), $context['isAdmin'] ? $context['customerId'] : null)
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->orderBy('metric_slug')
            ->limit(2000)
            ->get();
    }

    /**
     * @param  array{isAdmin: bool, customerId: ?int}  $context
     */
    protected function getDeviceInterfaceLogs(Request $request, Device $device, array $context, string $interfaceName)
    {
        return $this->userScope
            ->interfaceLogsQuery($request->user(), $context['isAdmin'] ? $context['customerId'] : null)
            ->where('device_id', $device->id)
            ->where('interface_name', $interfaceName)
            ->orderByDesc('recorded_at')
            ->limit(2000)
            ->get();
    }

    protected function formatLogRow($log): array
    {
        return [
            'id' => $log->id,
            'metric_slug' => $log->metric_slug,
            'metric_value' => $log->metric_value,
            'metric_text' => $log->metric_text,
            'recorded_at' => $log->recorded_at->format('M d, Y H:i:s'),
        ];
    }

    protected function formatInterfaceLogRow($log): array
    {
        return [
            'id' => $log->id,
            'interface_name' => $log->interface_name,
            'if_index' => $log->if_index ?? '—',
            'status' => ucfirst($log->status),
            'status_class' => strtolower($log->status),
            'rx' => $log->rx,
            'rx_fmt' => ByteFormatter::formatBytes($log->rx),
            'tx' => $log->tx,
            'tx_fmt' => ByteFormatter::formatBytes($log->tx),
            'rx_packets' => $log->rx_packets,
            'rx_packets_fmt' => ByteFormatter::formatPackets($log->rx_packets),
            'tx_packets' => $log->tx_packets,
            'tx_packets_fmt' => ByteFormatter::formatPackets($log->tx_packets),
            'recorded_at' => $log->recorded_at->format('M d, Y H:i:s'),
        ];
    }

    protected function reportFilename(Device $device, string $extension): string
    {
        $slug = preg_replace('/[^a-z0-9_-]+/i', '-', $device->name) ?: 'device';
        $slug = trim($slug, '-');

        return sprintf('%s-report-%s.%s', strtolower($slug), now()->format('Y-m-d'), $extension);
    }

    /**
     * @return array{
     *     isAdmin: bool,
     *     customerId: ?int,
     *     selectedCustomer: ?User,
     *     customers: \Illuminate\Support\Collection,
     *     from: Carbon,
     *     to: Carbon
     * }
     */
    protected function resolveReportFilters(Request $request): array
    {
        $user = $request->user();
        $isAdmin = (bool) $user->is_admin;
        $customerId = null;
        $selectedCustomer = null;
        $customers = collect();

        if ($isAdmin) {
            $customerId = $request->integer('user_id') ?: null;

            if ($customerId) {
                $selectedCustomer = User::query()
                    ->where('is_admin', false)
                    ->where('role', User::ROLE_USER)
                    ->find($customerId);

                if (! $selectedCustomer) {
                    $customerId = null;
                }
            }

            $customers = User::query()
                ->where('is_admin', false)
                ->where('role', User::ROLE_USER)
                ->orderBy('name')
                ->get();
        }

        $from = $request->date('from')?->startOfDay() ?? now()->subDays(7)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return compact('isAdmin', 'customerId', 'selectedCustomer', 'customers', 'from', 'to');
    }
}
