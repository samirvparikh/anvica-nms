<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\DeviceInterfaceLog;
use App\Models\DeviceMetricLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FaultManagementReportService
{
  /** @var array<string, array{warning: float, critical: float, type: string}> */
  protected array $metricRules = [
    'cpu' => ['warning' => 80, 'critical' => 95, 'type' => Alert::ALARM_HIGH_CPU],
    'ram' => ['warning' => 90, 'critical' => 95, 'type' => Alert::ALARM_HIGH_RAM],
    'disk' => ['warning' => 90, 'critical' => 95, 'type' => Alert::ALARM_DISK_USAGE],
    'temperature' => ['warning' => 70, 'critical' => 85, 'type' => Alert::ALARM_TEMPERATURE],
  ];

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

    $alertsInRange = $this->collectFaultEventsInRange($user, $customerId, $from, $to);
    $activeAlarms = $this->collectActiveFaults($user, $customerId);
    $downtimeEvents = $this->buildDowntimeEvents($user, $customerId, $from, $to);

    $previousFrom = $from->copy()->subDays(max(1, $from->diffInDays($to)));
    $previousTo = $from->copy()->subSecond();

    $previousAlerts = $this->collectFaultEventsInRange($user, $customerId, $previousFrom, $previousTo);
    $previousDowntimeEvents = $this->buildDowntimeEvents($user, $customerId, $previousFrom, $previousTo);

    $totalDowntimeSeconds = $this->sumDowntimeSeconds($downtimeEvents);
    $previousDowntimeSeconds = $this->sumDowntimeSeconds($previousDowntimeEvents);

    return [
      'range' => [
        'from' => $from->toIso8601String(),
        'to' => $to->toIso8601String(),
      ],
      'kpis' => [
        $this->kpi('Total Alerts', (string) $alertsInRange->count(), $this->trendPercent($alertsInRange->count(), $previousAlerts->count()), 'fa-solid fa-bell'),
        $this->kpiActiveAlarms($activeAlarms),
        $this->kpi('Downtime Events', (string) $downtimeEvents->count(), $this->trendDelta($downtimeEvents->count(), $previousDowntimeEvents->count()), 'fa-solid fa-power-off'),
        $this->kpi('Total Downtime', $this->formatDuration($totalDowntimeSeconds), $this->trendPercent($totalDowntimeSeconds, $previousDowntimeSeconds, true), 'fa-solid fa-clock'),
      ],
      'activeAlarms' => $activeAlarms->map(fn (array $fault) => $this->mapFaultRow($fault, true))->values()->all(),
      'downtimeSummary' => $downtimeEvents->map(fn (array $event) => $this->mapDowntimeRow($event))->values()->all(),
      'severitySummary' => $this->buildSeveritySummary($alertsInRange),
      'alarmsOverTime' => $this->buildAlarmsOverTime($alertsInRange, $from, $to),
      'featureBanner' => $this->featureBanner(),
    ];
  }

  /**
   * @return Collection<int, array<string, mixed>>
   */
  protected function collectFaultEventsInRange(User $user, ?int $customerId, Carbon $from, Carbon $to): Collection
  {
    $metricLogs = $this->userScope
      ->metricLogsQuery($user, $customerId)
      ->whereBetween('recorded_at', [$from, $to])
      ->with('device')
      ->orderBy('recorded_at')
      ->get();

    $interfaceLogs = $this->userScope
      ->interfaceLogsQuery($user, $customerId)
      ->whereBetween('recorded_at', [$from, $to])
      ->with('device')
      ->orderBy('recorded_at')
      ->get();

    $faults = collect();

    foreach ($metricLogs as $log) {
      $fault = $this->evaluateMetricLog($log);
      if ($fault) {
        $faults->push($fault);
      }
    }

    foreach ($interfaceLogs as $log) {
      if ($this->isInterfaceDown($log->status)) {
        $faults->push($this->mapInterfaceFault($log));
      }
    }

    return $faults;
  }

  /**
   * @return Collection<int, array<string, mixed>>
   */
  protected function collectActiveFaults(User $user, ?int $customerId): Collection
  {
    $latestMetricLogs = $this->latestMetricLogs($user, $customerId);
    $latestInterfaceLogs = $this->latestInterfaceLogs($user, $customerId);

    $faults = collect();

    foreach ($latestMetricLogs as $log) {
      $fault = $this->evaluateMetricLog($log);
      if ($fault) {
        $faults->push($fault);
      }
    }

    foreach ($latestInterfaceLogs as $log) {
      if ($this->isInterfaceDown($log->status)) {
        $faults->push($this->mapInterfaceFault($log));
      }
    }

    return $faults->sortByDesc('recorded_at')->values();
  }

  /**
   * @return Collection<int, DeviceMetricLog>
   */
  protected function latestMetricLogs(User $user, ?int $customerId): Collection
  {
    $deviceIds = $this->userScope->deviceIds($user, $customerId);

    if (empty($deviceIds)) {
      return collect();
    }

    $latestIds = DB::table('device_metrics_log')
      ->selectRaw('MAX(id) as id')
      ->whereIn('device_id', $deviceIds)
      ->groupBy('device_id', 'metric_slug')
      ->pluck('id');

    if ($latestIds->isEmpty()) {
      return collect();
    }

    return DeviceMetricLog::query()
      ->whereIn('id', $latestIds)
      ->with('device')
      ->get();
  }

  /**
   * @return Collection<int, DeviceInterfaceLog>
   */
  protected function latestInterfaceLogs(User $user, ?int $customerId): Collection
  {
    $deviceIds = $this->userScope->deviceIds($user, $customerId);

    if (empty($deviceIds)) {
      return collect();
    }

    $latestIds = DB::table('device_interface_log')
      ->selectRaw('MAX(id) as id')
      ->whereIn('device_id', $deviceIds)
      ->groupBy('device_id', 'interface_name')
      ->pluck('id');

    if ($latestIds->isEmpty()) {
      return collect();
    }

    return DeviceInterfaceLog::query()
      ->whereIn('id', $latestIds)
      ->with('device')
      ->get();
  }

  /**
   * @return Collection<int, array<string, mixed>>
   */
  protected function buildDowntimeEvents(User $user, ?int $customerId, Carbon $from, Carbon $to): Collection
  {
    $events = collect();

    $pingLogs = $this->userScope
      ->metricLogsQuery($user, $customerId)
      ->whereBetween('recorded_at', [$from, $to])
      ->where(function ($query) {
        $query->whereRaw('LOWER(metric_slug) LIKE ?', ['%ping%']);
      })
      ->with('device')
      ->orderBy('recorded_at')
      ->get()
      ->groupBy('device_id');

    foreach ($pingLogs as $group) {
      $events = $events->merge(
        $this->extractDowntimeFromStatusLogs($group, fn (DeviceMetricLog $log) => $this->isPingDown($log), 'Device Not Responding')
      );
    }



    return $events->sortByDesc('down_at')->values();
  }

  /**
   * @template T of DeviceMetricLog|DeviceInterfaceLog
   *
   * @param  Collection<int, T>  $logs
   * @param  callable(T): bool  $isDown
   * @param  callable(T): ?string|null  $labelResolver
   * @return Collection<int, array<string, mixed>>
   */
  protected function extractDowntimeFromStatusLogs(
    Collection $logs,
    callable $isDown,
    string $reason,
    ?callable $labelResolver = null,
  ): Collection {
    $events = collect();
    $openEvent = null;

    foreach ($logs as $log) {
      $down = $isDown($log);

      if ($down && $openEvent === null) {
        $openEvent = [
          'device' => $log->device,
          'down_at' => $log->recorded_at,
          'up_at' => null,
          'reason' => $labelResolver ? $reason . ': ' . $labelResolver($log) : $reason,
        ];

        continue;
      }

      if (! $down && $openEvent !== null) {
        $openEvent['up_at'] = $log->recorded_at;
        $events->push($openEvent);
        $openEvent = null;
      }
    }

    if ($openEvent !== null) {
      $events->push($openEvent);
    }

    return $events;
  }

  /**
   * @return array<string, mixed>|null
   */
  protected function evaluateMetricLog(DeviceMetricLog $log): ?array
  {
    if ($this->isMetaMetricSlug($log->metric_slug)) {
      return null;
    }

    $normalized = $this->normalizeMetricSlug($log->metric_slug);

    if ($normalized === 'ping') {
      if (! $this->isPingDown($log)) {
        return null;
      }

      return [
        'id' => $log->id,
        'device' => $log->device,
        'type' => Alert::ALARM_DEVICE_DOWN,
        'severity' => 'Critical',
        'recorded_at' => $log->recorded_at,
        'metric_slug' => $log->metric_slug,
        'metric_value' => (float) $log->metric_value,
      ];
    }

    if ($normalized === null || ! isset($this->metricRules[$normalized])) {
      return null;
    }

    $value = (float) $log->metric_value;
    if ($value <= 0 || ! $this->isThresholdMetricValue($normalized, $value)) {
      return null;
    }

    $rule = $this->metricRules[$normalized];
    $severity = null;

    if ($value >= $rule['critical']) {
      $severity = 'Critical';
    } elseif ($value >= $rule['warning']) {
      $severity = 'Major';
    }

    if ($severity === null) {
      return null;
    }

    return [
      'id' => $log->id,
      'device' => $log->device,
      'type' => $rule['type'],
      'severity' => $severity,
      'recorded_at' => $log->recorded_at,
      'metric_slug' => $log->metric_slug,
      'metric_value' => $value,
    ];
  }

  /**
   * @return array<string, mixed>
   */
  protected function mapInterfaceFault(DeviceInterfaceLog $log): array
  {
    return [
      'id' => $log->id,
      'device' => $log->device,
      'type' => Alert::ALARM_INTERFACE_DOWN,
      'severity' => 'Major',
      'recorded_at' => $log->recorded_at,
      'interface_name' => $log->interface_name,
    ];
  }

  /**
   * @param  array<string, mixed>  $fault
   */
  protected function mapFaultRow(array $fault, bool $active = false): array
  {
    $recordedAt = $fault['recorded_at'] instanceof Carbon
      ? $fault['recorded_at']
      : Carbon::parse($fault['recorded_at']);

    $device = $fault['device'] ?? null;

    return [
      'id' => 'LOG-' . str_pad((string) $fault['id'], 4, '0', STR_PAD_LEFT),
      'device' => $device?->name ?? 'Unknown',
      'ip' => $device?->ip_address ?? '—',
      'type' => $fault['type'],
      'severity' => $fault['severity'],
      'start' => $recordedAt->format('M d, Y H:i'),
      'duration' => $this->formatDuration((int) $recordedAt->diffInSeconds(now())),
      'status' => $active ? 'Active' : 'Resolved',
    ];
  }

  /**
   * @param  array<string, mixed>  $event
   */
  protected function mapDowntimeRow(array $event): array
  {
    $device = $event['device'] ?? null;
    $downAt = $event['down_at'] instanceof Carbon ? $event['down_at'] : Carbon::parse($event['down_at']);
    $upAt = $event['up_at'] instanceof Carbon ? $event['up_at'] : ($event['up_at'] ? Carbon::parse($event['up_at']) : null);
    $durationSeconds = $upAt
      ? (int) $downAt->diffInSeconds($upAt)
      : (int) $downAt->diffInSeconds(now());

    return [
      'device' => $device?->name ?? 'Unknown',
      'ip' => $device?->ip_address ?? '—',
      'down' => $downAt->format('M d, Y H:i'),
      'up' => $upAt?->format('M d, Y H:i') ?? '—',
      'duration' => $this->formatDuration($durationSeconds),
      'reason' => $event['reason'] ?? '—',
    ];
  }

  /**
   * @param  Collection<int, array<string, mixed>>  $faults
   * @return array{labels: array<int, string>, values: array<int, int>, colors: array<int, string>}
   */
  protected function buildSeveritySummary(Collection $faults): array
  {
    $groups = [
      'Critical' => 0,
      'Major' => 0,
      'Minor' => 0,
      'Warning' => 0,
    ];

    foreach ($faults as $fault) {
      $label = $fault['severity'] ?? 'Warning';
      $groups[$label] = ($groups[$label] ?? 0) + 1;
    }

    return [
      'labels' => array_keys($groups),
      'values' => array_values($groups),
      'colors' => ['#ef4444', '#f97316', '#3b82f6', '#eab308'],
    ];
  }

  /**
   * @param  Collection<int, array<string, mixed>>  $faults
   * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
   */
  protected function buildAlarmsOverTime(Collection $faults, Carbon $from, Carbon $to): array
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

        $count = $faults->filter(function (array $fault) use ($severity, $dayStart, $dayEnd) {
          $recorded = $fault['recorded_at'] instanceof Carbon
            ? $fault['recorded_at']
            : Carbon::parse($fault['recorded_at']);

          return ($fault['severity'] ?? '') === $severity
            && $recorded->between($dayStart, $dayEnd);
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
   * @param  Collection<int, array<string, mixed>>  $events
   */
  protected function sumDowntimeSeconds(Collection $events): int
  {
    return (int) $events->sum(function (array $event) {
      $downAt = $event['down_at'] instanceof Carbon ? $event['down_at'] : Carbon::parse($event['down_at']);
      $upAt = $event['up_at'] instanceof Carbon ? $event['up_at'] : ($event['up_at'] ? Carbon::parse($event['up_at']) : null);

      return $upAt
        ? (int) $downAt->diffInSeconds($upAt)
        : (int) $downAt->diffInSeconds(now());
    });
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
   * @param  Collection<int, array<string, mixed>>  $activeFaults
   */
  protected function kpiActiveAlarms(Collection $activeFaults): array
  {
    $counts = [
      'Critical' => 0,
      'Major' => 0,
      'Minor' => 0,
      'Warning' => 0,
    ];

    foreach ($activeFaults as $fault) {
      $counts[$fault['severity'] ?? 'Warning'] = ($counts[$fault['severity'] ?? 'Warning'] ?? 0) + 1;
    }

    $parts = [];
    foreach ($counts as $label => $count) {
      if ($count > 0) {
        $parts[] = $count . ' ' . $label;
      }
    }

    return [
      'label' => 'Active Alarms',
      'value' => (string) $activeFaults->count(),
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

  protected function normalizeMetricSlug(string $slug): ?string
  {
    $key = strtolower(str_replace([' ', '-'], '_', trim($slug)));

    return match (true) {
      in_array($key, ['cpu', 'cpu_usage', 'cpu_load'], true) => 'cpu',
      in_array($key, ['ram', 'ram_usage', 'memory'], true) => 'ram',
      in_array($key, ['disk', 'disk_usage', 'storage'], true) => 'disk',
      str_contains($key, 'temp') => 'temperature',
      str_contains($key, 'ping') => 'ping',
      default => null,
    };
  }

  protected function isMetaMetricSlug(string $slug): bool
  {
    $key = strtolower(str_replace([' ', '-'], '_', trim($slug)));

    return in_array($key, [
      'router',
      'host_name',
      'hostname',
      'name',
      'ip_address',
      'target_ip',
      'uptime',
      'total_ram',
      'ram_uses',
    ], true);
  }

  protected function isThresholdMetricValue(string $normalizedSlug, float $value): bool
  {
    if (in_array($normalizedSlug, ['cpu', 'ram', 'disk'], true) && $value > 100) {
      return false;
    }

    return true;
  }

  protected function isPingDown(DeviceMetricLog $log): bool
  {
    $text = strtoupper(trim((string) ($log->metric_text ?? '')));

    if (in_array($text, ['DOWN', 'OFFLINE', '0', 'FALSE', 'NO'], true)) {
      return true;
    }

    if (in_array($text, ['UP', 'ONLINE', '1', 'TRUE', 'YES'], true)) {
      return false;
    }

    return (float) $log->metric_value < 1;
  }

  protected function isInterfaceDown(?string $status): bool
  {
    $value = strtolower(trim((string) $status));

    return in_array($value, ['down', '0', 'false', 'offline', 'no'], true) || $value === '2';
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
