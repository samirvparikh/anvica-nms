<?php

namespace App\Models;

use App\Models\Concerns\ResolvesApplicationMasters;
use App\Support\ApplicationMasterHelper;
use App\Support\DeviceAssetMapper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class Device extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    public const HEALTH_UP = 'Up';

    public const HEALTH_WARNING = 'Warning';

    public const HEALTH_DOWN = 'Down';

    use HasFactory;
    use ResolvesApplicationMasters;

    protected $table = 'assets';

    /** @var array<string, string> */
    protected static array $legacyColumnMap = [
        'name' => 'asset_name',
        'ip_address' => 'management_ip',
        'user_id' => 'customer_id',
        'location' => 'site_location_id',
        'snmp_community' => 'snmp_community_user',
        'type' => 'asset_type_id',
        'device_type' => 'asset_type_id',
    ];

    public function qualifyColumn($column)
    {
        if (isset(static::$legacyColumnMap[$column])) {
            $column = static::$legacyColumnMap[$column];
        }

        return parent::qualifyColumn($column);
    }

    protected static function booted(): void
    {
        static::saving(function (Device $device): void {
            if (! $device->asset_name) {
                $device->asset_name = $device->attributes['asset_name'] ?? $device->attributes['name'] ?? 'Default Asset';
            }

            self::resolveMasterIdAttribute($device, 'asset_type_id', 'asset_type', [
                $device->attributes['asset_type'] ?? null,
                $device->attributes['type'] ?? null,
                $device->attributes['device_type'] ?? null,
                'Router',
            ]);

            self::resolveMasterIdAttribute($device, 'asset_category_id', 'asset_category', [
                $device->attributes['asset_category'] ?? null,
                'Network Infrastructure',
            ]);

            self::resolveMasterIdAttribute($device, 'status_id', 'asset_status', [
                $device->attributes['status'] ?? null,
                'Active',
            ]);

            if (! $device->exists && ! $device->asset_id_auto) {
                $year = date('Y');
                $count = Asset::whereYear('created_at', $year)->count() + 1;
                $device->asset_id_auto = sprintf('AST-%s-%04d', $year, $count);
            }

            self::resolveMasterIdAttribute($device, 'criticality_id', 'criticality', [
                $device->attributes['criticality'] ?? null,
                'Medium',
            ]);

            self::resolveMasterIdAttribute($device, 'manufacturer_id', 'manufacturer', [
                $device->attributes['manufacturer'] ?? null,
                'Cisco',
            ]);

            if (! $device->model_number) {
                $device->model_number = 'ISR 4331';
            }
            if (! $device->exists && ! $device->serial_number) {
                $device->serial_number = 'SN-'.uniqid();
            }
            if (! $device->customer_id) {
                $device->customer_id = $device->attributes['customer_id'] ?? $device->attributes['user_id'] ?? User::first()?->id ?? User::factory()->create()->id;
            }
            if (! $device->management_ip) {
                $device->management_ip = $device->attributes['management_ip'] ?? $device->attributes['ip_address'] ?? '127.0.0.1';
            }

            if (! $device->site_location_id && ! empty($device->attributes['location'])) {
                self::resolveMasterIdAttribute($device, 'site_location_id', 'site_location', [
                    $device->attributes['location'],
                ]);
            }
        });

        static::deleting(function (Device $device): void {
            foreach (['.rsc', '.src'] as $extension) {
                $scriptPath = public_path('scripts/device-'.$device->id.$extension);
                if (File::exists($scriptPath)) {
                    File::delete($scriptPath);
                }
            }
        });
    }

    protected $appends = [
        'name',
        'ip_address',
        'device_type',
        'type',
        'user_id',
        'location',
        'snmp_community',
    ];

    protected $fillable = [
        'customer_id',
        'service_id',
        'vendor_id',
        'asset_name',
        'hostname',
        'asset_type_id',
        'asset_category_id',
        'status_id',
        'criticality_id',
        'manufacturer_id',
        'site_location_id',
        'management_ip',
        'api_url',
        'api_username',
        'api_password',
        'snmp_version_id',
        'snmp_port',
        'snmp_community_user',
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
            'ssh_enabled' => 'boolean',
            'telnet_enabled' => 'boolean',
            'auto_discover_snmp' => 'boolean',
            'auto_import_interfaces' => 'boolean',
            'auto_import_software' => 'boolean',
            'auto_import_config_backup' => 'boolean',
            'health_monitoring' => 'boolean',
            'health_score_calculation' => 'boolean',
        ];
    }

    // Accessors and Mutators for backward compatibility
    public function getNameAttribute()
    {
        return $this->asset_name;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['asset_name'] = $value;
    }

    public function getIpAddressAttribute()
    {
        return $this->management_ip;
    }

    public function setIpAddressAttribute($value)
    {
        $this->attributes['management_ip'] = $value;
    }

    public function getDeviceTypeAttribute()
    {
        return $this->masterLabel('asset_type_id');
    }

    public function setDeviceTypeAttribute($value)
    {
        $this->setAttribute('asset_type_id', $value);
    }

    public function getTypeAttribute()
    {
        return $this->masterLabel('asset_type_id');
    }

    public function setTypeAttribute($value)
    {
        $this->setAttribute('asset_type_id', $value);
    }

    public function getUserIdAttribute()
    {
        return $this->customer_id;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['customer_id'] = $value;
    }

    public function getLocationAttribute()
    {
        return $this->masterLabel('site_location_id');
    }

    public function setLocationAttribute($value)
    {
        $this->setAttribute('site_location_id', $value);
    }

    public function getSnmpCommunityAttribute()
    {
        return $this->snmp_community_user;
    }

    public function setSnmpCommunityAttribute($value)
    {
        $this->attributes['snmp_community_user'] = $value;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(DeviceVendor::class, 'vendor_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(DeviceMetric::class, 'device_id');
    }

    public function metricLogs(): HasMany
    {
        return $this->hasMany(DeviceMetricLog::class, 'device_id');
    }

    public function interfaces(): HasMany
    {
        return $this->hasMany(DeviceInterface::class, 'device_id');
    }

    public function interfaceLogs(): HasMany
    {
        return $this->hasMany(DeviceInterfaceLog::class, 'device_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'device_id');
    }

    public function downtimeEvents(): HasMany
    {
        return $this->hasMany(DeviceDowntimeEvent::class, 'device_id');
    }

    public function script(): HasOne
    {
        return $this->hasOne(DeviceScript::class, 'device_id');
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

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            /** @var array<string, string> */
            protected array $legacyColumnMap = [
                'name' => 'asset_name',
                'ip_address' => 'management_ip',
                'user_id' => 'customer_id',
                'location' => 'site_location_id',
                'snmp_community' => 'snmp_community_user',
                'type' => 'asset_type_id',
                'device_type' => 'asset_type_id',
            ];

            protected function mapLegacyColumn(string $column): string
            {
                return $this->legacyColumnMap[$column] ?? $column;
            }

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    $column = $this->mapLegacyColumn($column);
                }

                return parent::where($column, $operator, $value, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if (is_string($column)) {
                    $column = $this->mapLegacyColumn($column);
                }

                return parent::orderBy($column, $direction);
            }
        };
    }

    /**
     * @param  array<int, mixed>  $candidates
     */
    protected static function resolveMasterIdAttribute(Device $device, string $column, string $masterType, array $candidates): void
    {
        if (! empty($device->attributes[$column])) {
            return;
        }

        if (DeviceAssetMapper::usesMasterIdColumns()) {
            foreach ($candidates as $candidate) {
                if ($candidate === null || $candidate === '') {
                    continue;
                }

                $resolvedId = ApplicationMasterHelper::resolveId($masterType, (string) $candidate);

                if ($resolvedId) {
                    $device->attributes[$column] = $resolvedId;

                    return;
                }
            }

            return;
        }

        $legacyColumn = match ($column) {
            'asset_type_id' => 'asset_type',
            'asset_category_id' => 'asset_category',
            'status_id' => 'status',
            'criticality_id' => 'criticality',
            'manufacturer_id' => 'manufacturer',
            'site_location_id' => 'site_location',
            default => null,
        };

        if (! $legacyColumn || ! Schema::hasColumn('assets', $legacyColumn)) {
            return;
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                $device->attributes[$legacyColumn] = $candidate;

                return;
            }
        }
    }
}
