<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMetric extends Model
{
    protected $fillable = [
        'device_id',
        'metric_slug',
        'metric_value',
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
