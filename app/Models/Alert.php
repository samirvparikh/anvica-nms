<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<\Database\Factories\AlertFactory> */
    use HasFactory;
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_INFO = 'info';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const ALARM_DEVICE_DOWN = 'Device Down';

    public const ALARM_HIGH_CPU = 'High CPU';

    public const ALARM_HIGH_RAM = 'High RAM';

    public const ALARM_DISK_USAGE = 'Disk Usage';

    public const ALARM_TEMPERATURE = 'Temperature';

    public const ALARM_INTERFACE_DOWN = 'Interface Down';

    public const ALARM_THRESHOLD = 'Threshold Violation';

    protected $fillable = [
        'device_id',
        'service_point_id',
        'alarm_type',
        'severity',
        'message',
        'status',
        'started_at',
        'resolved_at',
        'duration_seconds',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function servicePoint(): BelongsTo
    {
        return $this->belongsTo(ServicePoint::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
