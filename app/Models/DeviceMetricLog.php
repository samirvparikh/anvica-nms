<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMetricLog extends Model
{
    protected $table = 'device_metrics_log';

    protected $fillable = [
        'device_id',
        'metric_slug',
        'metric_value',
        'metric_text',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'metric_value' => 'decimal:4',
            'recorded_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
