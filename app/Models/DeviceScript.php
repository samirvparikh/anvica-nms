<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceScript extends Model
{
    protected $fillable = [
        'device_id',
        'target_ip',
        'snmp_community',
        'nms_url',
        'interface_indexes',
        'public_path',
    ];

    protected function casts(): array
    {
        return [
            'interface_indexes' => 'array',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function publicUrl(): string
    {
        return asset($this->public_path);
    }
}
