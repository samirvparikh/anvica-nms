<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceDowntimeEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FaultManagementReportService
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, ?int $customerId, Carbon $from, Carbon $to): array
    {
        $deviceIds = $this->userScope->deviceIds($user, $customerId);

        if (empty($deviceIds)) {
            return $this->emptyReport();
        }

        $alertsInRange = $this->alertsQuery($deviceIds)
            ->whereBetween('started_at', [$from, $to])
            ->with(['device'])
            ->get();

        $openAlerts = $this->alertsQuery($deviceIds)
            ->where('status', Alert::STATUS_OPEN)
            ->with(['device'])
            ->orderByDesc('started_at')
            ->get();

        $downtimeEvents = $this->downtimeQuery($deviceIds)
            ->where(function (Builder $query) use ($from, $to) {
                $query->whereBetween('down_at', [$from, $to])
                    ->orWhere(function (Builder $q) use ($from, $to) {
                        $q->where('down_at', '<=', $to)
                            ->where(function (Builder $inner) use ($from) {
                                $inner->whereNull('up_at')
                                    ->orWhere('up_at', '>=', $from);
                            });
                    });
            })
            ->with(['device'])
            ->orderByDesc('down_at')
            ->get();

        $previousFrom = $from->copy()->subDays($from->diffInDays($to) ?: 7);
        $previousTo = $from->copy()->subSecond();

        $previousAlertCount = $this->alertsQuery($deviceIds)
            ->whereBetween('started_at', [$previousFrom, $previousTo])
            ->count();

        $previousDowntimeCount = $this->downtimeQuery($deviceIds)
            ->whereBetween('down_at', [$previousFrom, $previousTo])
            ->count();

        $previousDowntimeSeconds = $this->sumDowntimeSeconds(
            $this->downtimeQuery($deviceIds)->whereBetween('down_at', [$previousFrom, $previousTo])->get()
        );

        $totalDowntimeSeconds = $this->sumDowntimeSeconds($downtimeEvents);

        return [
            'range' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'kpis' => [
                $this->kpi('Total Alerts', (string) $alertsInRange->count(), $this->trendPercent($alertsInRange->count(), $previousAlertCount), 'fa-solid fa-bell'),
                $this->kpiActiveAlarms($openAlerts),
                $this->kpi('Downtime Events', (string) $downtimeEvents->count(), $this->trendDelta($downtimeEvents->count(), $previousDowntimeCount), 'fa-solid fa-power-off'),
                $this->kpi('Total Downtime', $this->formatDuration($totalDowntimeSeconds), $this->trendPercent($totalDowntimeSeconds, $previousDowntimeSeconds, true), 'fa-solid fa-clock'),
            ],
            'activeAlarms' => $openAlerts->map(fn (Alert $alert) => $this->mapAlertRow($alert))->values()->all(),
            'downtimeSummary' => $downtimeEvents->map(fn (DeviceDowntimeEvent $event) => $this->mapDowntimeRow($event))->values()->all(),
            'severitySummary' => $this->buildSeveritySummary($alertsInRange),
            'alarmsOverTime' => $this->buildAlarmsOverTime($alertsInRange, $from, $to),
            'featureBanner' => $this->featureBanner(),
        ];
    }

    /**
     * @param  array<int>  $deviceIds
     */
    protected function alertsQuery(array $deviceIds): Builder
    {
        return Alert::query()->whereIn('device_id', $deviceIds);
    }

    /**
     * @param  array<int>  $deviceIds
     */
    protected function downtimeQuery(array $deviceIds): Builder
    {
        return DeviceDowntimeEvent::query()->whereIn('device_id', $deviceIds);
    }

    protected function mapAlertRow(Alert $alert): array
    {
        $startedAt = $alert->started_at ?? $alert->created_at;

        return [
            'id' => 'ALM-' . str_pad((string) $alert->id, 4, '0', STR_PAD_LEFT),
            'device' => $alert->device?->name ?? 'Unknown',
            'ip' => $alert->device?->ip_address ?? '—',
            'type' => $alert->alarm_type ?? $this->inferAlarmType($alert->message),
            'severity' => $this->displaySeverity($alert->severity),
            'start' => $startedAt->format('M d, Y H:i'),
            'duration' => $this->formatDuration((int) $startedAt->diffInSeconds(now())),
            'status' => ucfirst($alert->status === Alert::STATUS_OPEN ? 'Active' : $alert->status),
        ];
    }

    protected function mapDowntimeRow(DeviceDowntimeEvent $event): array
    {
        return [
            'device' => $event->device?->name ?? 'Unknown',
            'ip' => $event->device?->ip_address ?? '—',
            'down' => $event->down_at->format('M d, Y H:i'),
            'up' => $event->up_at?->format('M d, Y H:i') ?? '—',
            'duration' => $this->formatDuration($event->effectiveDurationSeconds()),
            'reason' => $event->reason ?? '—',
        ];
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     * @return array{labels: array<int, string>, values: array<int, int>, colors: array<int, string>}
     */
    protected function buildSeveritySummary(Collection $alerts): array
    {
        $groups = [
            'Critical' => 0,
            'Major' => 0,
            'Minor' => 0,
            'Warning' => 0,
        ];

        foreach ($alerts as $alert) {
            $label = $this->displaySeverity($alert->severity);
            $groups[$label] = ($groups[$label] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($groups),
            'values' => array_values($groups),
            'colors' => ['#ef4444', '#f97316', '#3b82f6', '#eab308'],
        ];
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    protected function buildAlarmsOverTime(Collection $alerts, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor <= $end) {
            $labels[] = $cursor->format('M d');
            $cursor->addDay();
        }

        $severities = ['Critical', 'Major', 'Minor', 'Warning'];
        $colors = ['#ef4444', '#f97316', '#3b82f6', '#eab308'];
        $datasets = [];

        foreach ($severities as $index => $severity) {
            $data = [];
            $cursor = $from->copy()->startOfDay();

            while ($cursor <= $end) {
                $dayStart = $cursor->copy()->startOfDay();
                $dayEnd = $cursor->copy()->endOfDay();

                $count = $alerts->filter(function (Alert $alert) use ($severity, $dayStart, $dayEnd) {
                    $started = $alert->started_at ?? $alert->created_at;

                    return $this->displaySeverity($alert->severity) === $severity
                        && $started->between($dayStart, $dayEnd);
                })->count();

                $data[] = $count;
                $cursor->addDay();
            }

            $datasets[] = [
                'label' => $severity,
                'data' => $data,
                'borderColor' => $colors[$index],
                'backgroundColor' => $colors[$index] . '1a',
                'tension' => 0.35,
                'fill' => false,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * @param  Collection<int, DeviceDowntimeEvent>  $events
     */
    protected function sumDowntimeSeconds(Collection $events): int
    {
        return (int) $events->sum(fn (DeviceDowntimeEvent $event) => $event->effectiveDurationSeconds());
    }

    protected function kpi(string $label, string $value, ?array $trend, string $icon): array
    {
        $item = [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
        ];

        if ($trend) {
            $item['trend'] = $trend['text'];
            $item['trendDir'] = $trend['dir'];
        }

        return $item;
    }

    /**
     * @param  Collection<int, Alert>  $openAlerts
     */
    protected function kpiActiveAlarms(Collection $openAlerts): array
    {
        $counts = [
            'Critical' => 0,
            'Major' => 0,
            'Minor' => 0,
            'Warning' => 0,
        ];

        foreach ($openAlerts as $alert) {
            $counts[$this->displaySeverity($alert->severity)]++;
        }

        $parts = [];
        foreach ($counts as $label => $count) {
            if ($count > 0) {
                $parts[] = $count . ' ' . $label;
            }
        }

        return [
            'label' => 'Active Alarms',
            'value' => (string) $openAlerts->count(),
            'subtitle' => $parts ? implode(' · ', $parts) : 'No active alarms',
            'icon' => 'fa-solid fa-triangle-exclamation',
        ];
    }

    protected function trendPercent(int|float $current, int|float $previous, bool $inverse = false): ?array
    {
        if ($previous <= 0) {
            return $current > 0
                ? ['text' => '▲ new activity', 'dir' => 'up']
                : null;
        }

        $change = (($current - $previous) / $previous) * 100;
        $rounded = abs(round($change, 0));

        if ($inverse) {
            $isGood = $change < 0;
        } else {
            $isGood = false;
        }

        $dir = $change >= 0 ? 'up' : 'down';

        if ($inverse) {
            $dir = $change <= 0 ? 'down' : 'up';
        }

        $arrow = $change >= 0 ? '▲' : '▼';

        return [
            'text' => $arrow . ' ' . $rounded . '% vs previous period',
            'dir' => $dir,
        ];
    }

    protected function trendDelta(int $current, int $previous): ?array
    {
        $delta = $current - $previous;
        if ($delta === 0) {
            return null;
        }

        return [
            'text' => ($delta > 0 ? '▲ ' : '▼ ') . abs($delta) . ' vs previous period',
            'dir' => $delta > 0 ? 'up' : 'down',
        ];
    }

    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return max(1, $minutes) . 'm';
    }

    protected function displaySeverity(string $severity): string
    {
        return match (strtolower($severity)) {
            Alert::SEVERITY_CRITICAL, 'critical' => 'Critical',
            'major' => 'Major',
            'minor' => 'Minor',
            Alert::SEVERITY_WARNING, 'warning' => 'Major',
            Alert::SEVERITY_INFO, 'info' => 'Minor',
            default => 'Warning',
        };
    }

    protected function inferAlarmType(?string $message): string
    {
        $message = strtolower($message ?? '');

        if (str_contains($message, 'offline') || str_contains($message, 'down')) {
            return 'Device Down';
        }

        if (str_contains($message, 'cpu')) {
            return 'High CPU';
        }

        if (str_contains($message, 'ram')) {
            return 'High RAM';
        }

        if (str_contains($message, 'disk')) {
            return 'Disk Usage';
        }

        if (str_contains($message, 'temperature') || str_contains($message, 'temp')) {
            return 'Temperature';
        }

        if (str_contains($message, 'interface')) {
            return 'Interface Down';
        }

        return 'Threshold Violation';
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function featureBanner(): array
    {
        return [
            ['icon' => 'fa-solid fa-satellite-dish', 'title' => 'Real-Time Monitoring', 'text' => 'Monitor your network 24×7 with live device status and health metrics.'],
            ['icon' => 'fa-solid fa-bolt', 'title' => 'Instant Alerts', 'text' => 'Get notified before issues become major outages.'],
            ['icon' => 'fa-solid fa-chart-pie', 'title' => 'Performance Insights', 'text' => 'Analyze trends and optimize network performance.'],
            ['icon' => 'fa-solid fa-shield-halved', 'title' => 'Reduce Downtime', 'text' => 'Detect problems early and reduce downtime.'],
            ['icon' => 'fa-solid fa-clipboard-check', 'title' => 'SLA Compliance', 'text' => 'Stay compliant and deliver better service quality.'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyReport(): array
    {
        return [
            'range' => [],
            'kpis' => [
                $this->kpi('Total Alerts', '0', null, 'fa-solid fa-bell'),
                ['label' => 'Active Alarms', 'value' => '0', 'subtitle' => 'No active alarms', 'icon' => 'fa-solid fa-triangle-exclamation'],
                $this->kpi('Downtime Events', '0', null, 'fa-solid fa-power-off'),
                $this->kpi('Total Downtime', '0m', null, 'fa-solid fa-clock'),
            ],
            'activeAlarms' => [],
            'downtimeSummary' => [],
            'severitySummary' => [
                'labels' => ['Critical', 'Major', 'Minor', 'Warning'],
                'values' => [0, 0, 0, 0],
                'colors' => ['#ef4444', '#f97316', '#3b82f6', '#eab308'],
            ],
            'alarmsOverTime' => [
                'labels' => [],
                'datasets' => [],
            ],
            'featureBanner' => $this->featureBanner(),
        ];
    }
}
