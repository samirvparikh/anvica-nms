<?php

namespace App\Support;

class ByteFormatter
{
    private const BYTES_PER_MB = 1048576;

    public static function toMegabytes(int|float|string|null $bytes, int $decimals = 2): string
    {
        if ($bytes === null || $bytes === '') {
            return '—';
        }

        $bytes = (float) $bytes;

        if ($bytes <= 0) {
            return '0 MB';
        }

        $megabytes = $bytes / self::BYTES_PER_MB;

        return number_format($megabytes, $decimals).' MB';
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
