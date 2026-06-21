<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceDowntimeEvent extends Model
{
    public const SOURCE_POLL = 'poll';

    public const SOURCE_PUSH = 'push';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'device_id',
        'down_at',
        'up_at',
        'duration_seconds',
        'reason',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'down_at' => 'datetime',
            'up_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isOngoing(): bool
    {
        return $this->up_at === null;
    }

    public function effectiveDurationSeconds(): int
    {
        if ($this->duration_seconds !== null) {
            return $this->duration_seconds;
        }

        if ($this->down_at === null) {
            return 0;
        }

        return (int) $this->down_at->diffInSeconds($this->up_at ?? now());
    }
}
