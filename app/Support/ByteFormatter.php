<?php

namespace App\Support;

class ByteFormatter
{
    private const UNIT = 1024;

    /**
     * Auto-scale bytes: B → KB → MB → GB → TB (1024-based).
     */
    public static function formatBytes(int|float|string|null $bytes, int $decimals = 2): string
    {
        if ($bytes === null || $bytes === '') {
            return '—';
        }

        $bytes = (float) $bytes;

        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        $value = $bytes;

        while ($value >= self::UNIT && $unitIndex < count($units) - 1) {
            $value /= self::UNIT;
            $unitIndex++;
        }

        return number_format($value, $decimals).' '.$units[$unitIndex];
    }

    /** @deprecated Use formatBytes() */
    public static function toMegabytes(int|float|string|null $bytes, int $decimals = 2): string
    {
        return self::formatBytes($bytes, $decimals);
    }

    public static function formatPackets(int|float|string|null $packets, int $decimals = 2): string
    {
        if ($packets === null || $packets === '') {
            return '—';
        }

        $packets = (float) $packets;

        if ($packets <= 0) {
            return '0';
        }

        if ($packets >= 1_000_000_000) {
            return number_format($packets / 1_000_000_000, $decimals).' B';
        }

        if ($packets >= 1_000_000) {
            return number_format($packets / 1_000_000, $decimals).' M';
        }

        if ($packets >= 1_000) {
            return number_format($packets / 1_000, $decimals).' K';
        }

        return number_format($packets);
    }
}
