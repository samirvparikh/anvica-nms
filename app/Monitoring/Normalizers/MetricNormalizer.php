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
