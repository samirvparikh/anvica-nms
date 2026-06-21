<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceInterfaceLog extends Model
{
    protected $table = 'device_interface_log';

    protected $fillable = [
        'device_id',
        'interface_name',
        'if_index',
        'status',
        'rx',
        'tx',
        'rx_packets',
        'tx_packets',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'rx' => 'integer',
            'tx' => 'integer',
            'rx_packets' => 'integer',
            'tx_packets' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
