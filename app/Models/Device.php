<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\File;

class Device extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const HEALTH_UP = 'Up';

    public const HEALTH_WARNING = 'Warning';

    public const HEALTH_DOWN = 'Down';

    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Device $device): void {
            foreach (['.rsc', '.src'] as $extension) {
                $scriptPath = public_path('scripts/device-'.$device->id.$extension);
                if (File::exists($scriptPath)) {
                    File::delete($scriptPath);
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'service_id',
        'vendor_id',
        'name',
        'hostname',
        'type',
        'device_type',
        'ip_address',
        'location',
        'api_url',
        'api_username',
        'api_password',
        'snmp_version',
        'snmp_port',
        'snmp_community',
        'status',
        'health_status',
        'last_seen',
    ];

    protected $hidden = [
        'api_password',
    ];

    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
            'api_password' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(DeviceVendor::class, 'vendor_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(DeviceMetric::class);
    }

    public function metricLogs(): HasMany
    {
        return $this->hasMany(DeviceMetricLog::class);
    }

    public function interfaces(): HasMany
    {
        return $this->hasMany(DeviceInterface::class);
    }

    public function interfaceLogs(): HasMany
    {
        return $this->hasMany(DeviceInterfaceLog::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function downtimeEvents(): HasMany
    {
        return $this->hasMany(DeviceDowntimeEvent::class);
    }

    public function script(): HasOne
    {
        return $this->hasOne(DeviceScript::class);
    }

    public function driverSlug(): ?string
    {
        return $this->vendor?->slug;
    }

    public function healthScore(): int
    {
        if ($this->health_status === self::HEALTH_DOWN) {
            return 0;
        }

        $cpu = (float) $this->metrics()->where('metric_slug', 'cpu')->latest('recorded_at')->value('metric_value');
        $ram = (float) $this->metrics()->where('metric_slug', 'ram')->latest('recorded_at')->value('metric_value');

        if ($cpu === 0.0 && $ram === 0.0) {
            return $this->health_status === self::HEALTH_WARNING ? 60 : 100;
        }

        $score = 100;
        $score -= min(40, max(0, $cpu - 50) * 0.8);
        $score -= min(40, max(0, $ram - 60) * 0.8);

        return (int) max(0, min(100, round($score)));
    }
}
