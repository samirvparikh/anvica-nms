<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CronLog extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'command',
        'status',
        'message',
        'affected',
        'exit_code',
        'started_at',
        'finished_at',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'affected' => 'integer',
            'exit_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * Record the start of a command run.
     */
    public static function start(string $command): self
    {
        return static::create([
            'command' => $command,
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark this run as finished with a status and optional details.
     */
    public function finish(string $status, ?string $message = null, int $affected = 0, ?int $exitCode = null): self
    {
        $finishedAt = now();
        $startedAt = $this->started_at ?? $finishedAt;

        $this->update([
            'status' => $status,
            'message' => $message,
            'affected' => $affected,
            'exit_code' => $exitCode,
            'finished_at' => $finishedAt,
            'duration_ms' => (int) ($startedAt->diffInMilliseconds($finishedAt)),
        ]);

        return $this;
    }

    public function markSuccess(?string $message = null, int $affected = 0): self
    {
        return $this->finish(self::STATUS_SUCCESS, $message, $affected, 0);
    }

    public function markFailed(?string $message = null, ?int $exitCode = 1): self
    {
        return $this->finish(self::STATUS_FAILED, $message, 0, $exitCode);
    }
}
