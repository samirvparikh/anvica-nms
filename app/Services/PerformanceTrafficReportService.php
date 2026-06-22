<?php

namespace App\Services;

use App\Models\DeviceInterfaceLog;
use App\Models\DeviceMetricLog;
use App\Models\User;
use App\Support\LatencyFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PerformanceTrafficReportService
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
            return $this->emptyReport($from, $to);
        }

        $metricLogs = $this->userScope
            ->metricLogsQuery($user, $customerId)
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get();

        $interfaceLogs = $this->userScope
            ->interfaceLogsQuery($user, $customerId)
            ->whereBetween('recorded_at', [$from, $to])
            ->with('device')
            ->orderBy('recorded_at')
            ->get();

        $labels = $this->buildDayLabels($from, $to);
        $bandwidthTrend = $this->buildDailyBandwidthTrend($interfaceLogs, $from, $to);
        $latencyTrend = $this->buildDailyMetricTrend($metricLogs, $from, $to, 'latency');
        $packetLossTrend = $this->buildDailyMetricTrend($metricLogs, $from, $to, 'packet_loss');
        $cpuTrend = $this->buildDailyMetricTrend($metricLogs, $from, $to, 'cpu');
        $memoryTrend = $this->buildDailyMetricTrend($metricLogs, $from, $to, 'memory');

        $previousFrom = $from->copy()->subDays(max(1, $from->diffInDays($to)));
        $previousTo = $from->copy()->subSecond();

        $previousMetricLogs = $this->userScope
            ->metricLogsQuery($user, $customerId)
            ->whereBetween('recorded_at', [$previousFrom, $previousTo])
            ->get();

        $previousInterfaceLogs = $this->userScope
            ->interfaceLogsQuery($user, $customerId)
            ->whereBetween('recorded_at', [$previousFrom, $previousTo])
            ->get();

        $bandwidthAvg = $this->averageTrend($bandwidthTrend);
        $latencyAvg = $this->averageTrend($latencyTrend);
        $packetLossAvg = $this->averageTrend($packetLossTrend);
        $cpuAvg = $this->averageTrend($cpuTrend);
        $memoryAvg = $this->averageTrend($memoryTrend);

        $previousBandwidth = $this->averageTrend($this->buildDailyBandwidthTrend($previousInterfaceLogs, $previousFrom, $previousTo));
        $previousLatency = $this->averageTrend($this->buildDailyMetricTrend($previousMetricLogs, $previousFrom, $previousTo, 'latency'));
        $previousPacketLoss = $this->averageTrend($this->buildDailyMetricTrend($previousMetricLogs, $previousFrom, $previousTo, 'packet_loss'));
        $previousCpu = $this->averageTrend($this->buildDailyMetricTrend($previousMetricLogs, $previousFrom, $previousTo, 'cpu'));
        $previousMemory = $this->averageTrend($this->buildDailyMetricTrend($previousMetricLogs, $previousFrom, $previousTo, 'memory'));

        return [
            'range' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'kpis' => [
                $this->kpi('Bandwidth Utilization', $this->formatPercent($bandwidthAvg), $this->trendPercent($bandwidthAvg, $previousBandwidth), 'fa-solid fa-gauge-high'),
                $this->kpi('Average Latency', $this->formatLatency($latencyAvg), $this->trendPercent($latencyAvg, $previousLatency, true), 'fa-solid fa-stopwatch'),
                $this->kpi('Packet Loss', $this->formatPercent($packetLossAvg), $this->trendPercent($packetLossAvg, $previousPacketLoss, true), 'fa-solid fa-wave-square'),
                $this->kpi('CPU Utilization', $this->formatPercent($cpuAvg), $this->trendPercent($cpuAvg, $previousCpu), 'fa-solid fa-microchip'),
                $this->kpi('Memory Utilization', $this->formatPercent($memoryAvg), $this->trendPercent($memoryAvg, $previousMemory), 'fa-solid fa-memory'),
            ],
            'trendLabels' => $labels,
            'bandwidthTrend' => $bandwidthTrend,
            'latencyTrend' => $latencyTrend,
            'packetLossTrend' => $packetLossTrend,
            'cpuTrend' => $cpuTrend,
            'memoryTrend' => $memoryTrend,
            'topInterfaces' => $this->buildTopInterfaces($interfaceLogs),
        ];
    }

    /**
     * @return list<string>
     */
    protected function buildDayLabels(Carbon $from, Carbon $to): array
    {
        $labels = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor <= $end) {
            $labels[] = $cursor->format('M d');
            $cursor->addDay();
        }

        return $labels;
    }

    /**
     * @param  Collection<int, DeviceMetricLog>  $logs
     * @return list<float>
     */
    protected function buildDailyMetricTrend(Collection $logs, Carbon $from, Carbon $to, string $category): array
    {
        $dailyValues = [];

        foreach ($logs as $log) {
            if ($this->metricCategory($log->metric_slug) !== $category) {
                continue;
            }

            $value = $this->extractMetricValue($log, $category);
            if ($value === null) {
                continue;
            }

            $day = $log->recorded_at->format('Y-m-d');
            $dailyValues[$day][] = $value;
        }

        $trend = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor <= $end) {
            $day = $cursor->format('Y-m-d');
            $values = $dailyValues[$day] ?? [];
            $trend[] = $values === [] ? 0.0 : round(array_sum($values) / count($values), 2);
            $cursor->addDay();
        }

        return $trend;
    }

    /**
     * @param  Collection<int, DeviceInterfaceLog>  $logs
     * @return list<float>
     */
    protected function buildDailyBandwidthTrend(Collection $logs, Carbon $from, Carbon $to): array
    {
        $dailyMbps = [];
        $groups = $logs->groupBy(fn (DeviceInterfaceLog $log) => $log->device_id . '|' . $log->interface_name);

        foreach ($groups as $group) {
            $sorted = $group->sortBy('recorded_at')->values();

            for ($index = 1; $index < $sorted->count(); $index++) {
                /** @var DeviceInterfaceLog $previous */
                $previous = $sorted[$index - 1];
                /** @var DeviceInterfaceLog $current */
                $current = $sorted[$index];

                $mbps = $this->throughputMbps($previous, $current);
                if ($mbps === null) {
                    continue;
                }

                $day = $current->recorded_at->format('Y-m-d');
                $dailyMbps[$day][] = $mbps;
            }
        }

        $trend = [];
        $peak = 0.0;
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor <= $end) {
            $day = $cursor->format('Y-m-d');
            $values = $dailyMbps[$day] ?? [];
            $avg = $values === [] ? 0.0 : array_sum($values) / count($values);
            $peak = max($peak, $avg);
            $trend[] = round($avg, 2);
            $cursor->addDay();
        }

        if ($peak <= 0) {
            return $trend;
        }

        return array_map(fn (float $value) => round(($value / $peak) * 100, 2), $trend);
    }

    /**
     * @param  Collection<int, DeviceInterfaceLog>  $logs
     * @return list<array<string, mixed>>
     */
    protected function buildTopInterfaces(Collection $logs): array
    {
        $rows = [];

        foreach ($logs->groupBy(fn (DeviceInterfaceLog $log) => $log->device_id . '|' . $log->interface_name) as $group) {
            $sorted = $group->sortBy('recorded_at')->values();
            if ($sorted->count() < 2) {
                continue;
            }

            $inRates = [];
            $outRates = [];
            $combinedRates = [];

            for ($index = 1; $index < $sorted->count(); $index++) {
                /** @var DeviceInterfaceLog $previous */
                $previous = $sorted[$index - 1];
                /** @var DeviceInterfaceLog $current */
                $current = $sorted[$index];
                $seconds = max(1, $previous->recorded_at->diffInSeconds($current->recorded_at));

                $deltaRx = max(0, $current->rx - $previous->rx);
                $deltaTx = max(0, $current->tx - $previous->tx);

                if ($deltaRx + $deltaTx <= 0) {
                    continue;
                }

                $inRates[] = ($deltaRx * 8) / ($seconds * 1_000_000);
                $outRates[] = ($deltaTx * 8) / ($seconds * 1_000_000);
                $combinedRates[] = (($deltaRx + $deltaTx) * 8) / ($seconds * 1_000_000);
            }

            if ($combinedRates === []) {
                continue;
            }

            /** @var DeviceInterfaceLog $latest */
            $latest = $sorted->last();

            $rows[] = [
                'interface' => $latest->interface_name,
                'device' => $latest->device?->name ?? 'Unknown',
                'inTraffic' => $this->formatMbps(array_sum($inRates) / count($inRates)),
                'outTraffic' => $this->formatMbps(array_sum($outRates) / count($outRates)),
                'avgMbps' => array_sum($combinedRates) / count($combinedRates),
            ];
        }

        usort($rows, fn (array $a, array $b) => $b['avgMbps'] <=> $a['avgMbps']);
        $rows = array_slice($rows, 0, 10);

        $peak = $rows[0]['avgMbps'] ?? 0;

        return array_map(function (array $row) use ($peak) {
            $avgMbps = $row['avgMbps'];
            unset($row['avgMbps']);
            $row['utilization'] = $peak > 0 ? (int) round(($avgMbps / $peak) * 100) : 0;

            return $row;
        }, $rows);
    }

    protected function throughputMbps(DeviceInterfaceLog $previous, DeviceInterfaceLog $current): ?float
    {
        $seconds = max(1, $previous->recorded_at->diffInSeconds($current->recorded_at));
        $deltaBytes = max(0, ($current->rx + $current->tx) - ($previous->rx + $previous->tx));

        if ($deltaBytes <= 0) {
            return null;
        }

        return ($deltaBytes * 8) / ($seconds * 1_000_000);
    }

    protected function metricCategory(string $slug): ?string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim($slug)));

        return match (true) {
            in_array($key, ['cpu', 'cpu_usage', 'cpu_load'], true) => 'cpu',
            in_array($key, ['ram', 'ram_usage', 'memory'], true) => 'memory',
            in_array($key, ['latency', 'ping_latency', 'ping_time', 'latency_time'], true) => 'latency',
            str_contains($key, 'latency') => 'latency',
            str_contains($key, 'ping') && str_contains($key, 'time') => 'latency',
            str_contains($key, 'packet') && str_contains($key, 'loss') => 'packet_loss',
            default => null,
        };
    }

    protected function extractMetricValue(DeviceMetricLog $log, string $category): ?float
    {
        return match ($category) {
            'cpu', 'memory', 'packet_loss' => $this->extractPercentValue($log),
            'latency' => LatencyFormatter::toMilliseconds($log->metric_value, $log->metric_text, $log->metric_slug),
            default => null,
        };
    }

    protected function extractPercentValue(DeviceMetricLog $log): ?float
    {
        $value = (float) $log->metric_value;

        if ($value <= 0) {
            return null;
        }

        if ($value <= 100) {
            return $value;
        }

        return null;
    }

    /**
     * @param  list<float>  $trend
     */
    protected function averageTrend(array $trend): float
    {
        if ($trend === []) {
            return 0.0;
        }

        $values = array_values(array_filter($trend, fn (float $value) => $value > 0));

        if ($values === []) {
            return 0.0;
        }

        return round(array_sum($values) / count($values), 2);
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

    protected function trendPercent(float $current, float $previous, bool $inverse = false): ?array
    {
        if ($current <= 0 && $previous <= 0) {
            return null;
        }

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

    protected function formatPercent(float $value): string
    {
        return number_format($value, $value < 10 ? 2 : 1) . '%';
    }

    protected function formatLatency(float $value): string
    {
        if ($value <= 0) {
            return '—';
        }

        return number_format($value, $value < 10 ? 2 : 1) . ' ms';
    }

    protected function formatMbps(float $value): string
    {
        if ($value <= 0) {
            return '—';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, 2) . ' Gbps';
        }

        return number_format($value, 1) . ' Mbps';
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyReport(Carbon $from, Carbon $to): array
    {
        $labels = $this->buildDayLabels($from, $to);
        $zeros = array_fill(0, count($labels), 0.0);

        return [
            'range' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'kpis' => [
                $this->kpi('Bandwidth Utilization', '0%', null, 'fa-solid fa-gauge-high'),
                $this->kpi('Average Latency', '—', null, 'fa-solid fa-stopwatch'),
                $this->kpi('Packet Loss', '0%', null, 'fa-solid fa-wave-square'),
                $this->kpi('CPU Utilization', '0%', null, 'fa-solid fa-microchip'),
                $this->kpi('Memory Utilization', '0%', null, 'fa-solid fa-memory'),
            ],
            'trendLabels' => $labels,
            'bandwidthTrend' => $zeros,
            'latencyTrend' => $zeros,
            'packetLossTrend' => $zeros,
            'cpuTrend' => $zeros,
            'memoryTrend' => $zeros,
            'topInterfaces' => [],
        ];
    }
}
