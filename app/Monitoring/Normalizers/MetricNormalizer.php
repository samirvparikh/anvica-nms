<?php

namespace App\Monitoring\Normalizers;

class MetricNormalizer
{
    public static function fromMikroTik(array $raw): array
    {
        $system = $raw['SYSTEM'] ?? [];
        $ram = self::parseFraction($system['RAM_Used'] ?? '0/0');
        $disk = self::parseFraction($system['Disk_Used'] ?? '0/0');

        return [
            'hostname' => $system['Router'] ?? null,
            'cpu' => self::parsePercent($system['CPU'] ?? '0'),
            'ram_used' => $ram['used'],
            'ram_total' => $ram['total'],
            'ram' => $ram['total'] > 0 ? round(($ram['used'] / $ram['total']) * 100, 2) : 0,
            'disk_used' => $disk['used'],
            'disk_total' => $disk['total'],
            'disk' => $disk['total'] > 0 ? round(($disk['used'] / $disk['total']) * 100, 2) : 0,
            'uptime' => $system['Uptime'] ?? null,
            'temperature' => isset($system['Temperature']) ? (float) $system['Temperature'] : null,
        ];
    }

    public static function fromGeneric(array $raw): array
    {
        return [
            'hostname' => $raw['hostname'] ?? null,
            'cpu' => (float) ($raw['cpu'] ?? 0),
            'ram_used' => (int) ($raw['ram_used'] ?? 0),
            'ram_total' => (int) ($raw['ram_total'] ?? 0),
            'ram' => (float) ($raw['ram'] ?? 0),
            'disk_used' => (int) ($raw['disk_used'] ?? 0),
            'disk_total' => (int) ($raw['disk_total'] ?? 0),
            'disk' => (float) ($raw['disk'] ?? 0),
            'uptime' => $raw['uptime'] ?? null,
            'temperature' => isset($raw['temperature']) ? (float) $raw['temperature'] : null,
        ];
    }

    /**
     * Flat router push via GET/POST query params (MikroTik script).
     *
     * @return array{hostname: ?string, uptime: mixed, metrics: array<string, float|int>}
     */
    public static function fromRouterPush(array $raw): array
    {
        $ramUsed = (int) ($raw['Ram_Uses'] ?? $raw['ram_uses'] ?? 0);
        $ramTotal = (int) ($raw['Total_Ram'] ?? $raw['total_ram'] ?? 0);
        $cpuTemp = (float) ($raw['CPU_Temp'] ?? $raw['cpu_temp'] ?? 0);
        $boardTemp = (float) ($raw['Board_Temp'] ?? $raw['board_temp'] ?? 0);

        $metrics = [
            'cpu' => self::parsePercent((string) ($raw['CPU'] ?? $raw['cpu'] ?? '0')),
            'cpu_temp' => $cpuTemp,
            'board_temp' => $boardTemp,
            'temperature' => max($cpuTemp, $boardTemp),
            'ram_uses' => $ramUsed,
            'total_ram' => $ramTotal,
            'ram' => $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0,
            'up_time' => (float) ($raw['UP_time'] ?? $raw['up_time'] ?? 0),
            'power1_status' => (float) ($raw['Power1_Status'] ?? $raw['power1_status'] ?? 0),
            'power2_status' => (float) ($raw['Power2_Status'] ?? $raw['power2_status'] ?? 0),
        ];

        return [
            'hostname' => $raw['Host_Name'] ?? $raw['host_name'] ?? $raw['Router'] ?? $raw['router'] ?? null,
            'uptime' => $raw['UP_time'] ?? $raw['up_time'] ?? null,
            'metrics' => $metrics,
        ];
    }

    public static function isFlatRouterPush(array $raw): bool
    {
        return isset($raw['Router'])
            || isset($raw['router'])
            || isset($raw['CPU'])
            || isset($raw['cpu'])
            || isset($raw['IP_Address'])
            || isset($raw['ip_address']);
    }

    public static function parsePercent(string $value): float
    {
        return (float) str_replace('%', '', trim($value));
    }

    /**
     * @return array{used: int, total: int}
     */
    public static function parseFraction(string $value): array
    {
        [$used, $total] = array_pad(explode('/', $value), 2, 0);

        return [
            'used' => (int) $used,
            'total' => (int) $total,
        ];
    }
}
