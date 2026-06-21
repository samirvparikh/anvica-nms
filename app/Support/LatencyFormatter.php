<?php

namespace App\Support;

class LatencyFormatter
{
    /**
     * Format latency as milliseconds for display.
     */
    public static function formatMilliseconds(int|float|string|null $value, ?string $text = null, ?string $metricSlug = null, int $decimals = 1): string
    {
        $ms = self::toMilliseconds($value, $text, $metricSlug);

        if ($ms === null) {
            return '—';
        }

        return number_format($ms, $decimals).' ms';
    }

    /**
     * Normalize latency from stored metric value/text to milliseconds.
     */
    public static function toMilliseconds(int|float|string|null $value, ?string $text = null, ?string $metricSlug = null): ?float
    {
        $text = trim((string) ($text ?? ''));

        if ($text !== '' && $text !== '—') {
            $parsed = self::parseLatencyString($text);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return self::parseLatencyString((string) $value);
        }

        $numeric = (float) $value;

        if ($numeric <= 0) {
            return null;
        }

        if (self::metricSlugIsSeconds($metricSlug) && $numeric < 60) {
            return $numeric * 1000;
        }

        // Sub-second values are usually seconds (e.g. 0.012 from ping tools).
        if ($numeric < 1) {
            return $numeric * 1000;
        }

        // Very large raw values are often microseconds.
        if ($numeric >= 100_000) {
            return $numeric / 1000;
        }

        return $numeric;
    }

    protected static function metricSlugIsSeconds(?string $metricSlug): bool
    {
        if ($metricSlug === null || $metricSlug === '') {
            return false;
        }

        $slug = strtolower(str_replace([' ', '-'], '_', $metricSlug));

        return in_array($slug, ['ping_time', 'ping_latency', 'latency_time'], true)
            || (str_contains($slug, 'ping') && str_contains($slug, 'time'));
    }

    protected static function parseLatencyString(string $text): ?float
    {
        $normalized = strtolower(trim($text));

        if ($normalized === '' || in_array($normalized, ['up', 'down', 'online', 'offline', 'n/a', 'na', '-'], true)) {
            return null;
        }

        if (preg_match('/^([\d.]+)\s*(ms|millisecond|milliseconds|s|sec|secs|second|seconds|us|µs|μs)?$/i', $normalized, $matches)) {
            $number = (float) $matches[1];
            $unit = strtolower($matches[2] ?? '');

            if ($unit === '' || str_starts_with($unit, 'ms') || str_contains($unit, 'milli')) {
                return $number;
            }

            if (in_array($unit, ['s', 'sec', 'secs', 'second', 'seconds'], true)) {
                return $number * 1000;
            }

            if (in_array($unit, ['us', 'µs', 'μs'], true)) {
                return $number / 1000;
            }

            return $number;
        }

        if (is_numeric($normalized)) {
            return self::toMilliseconds($normalized, null);
        }

        return null;
    }
}
