<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceInterface extends Model
{
    protected $fillable = [
        'device_id',
        'interface_name',
        'status',
        'rx',
        'tx',
        'rx_packets',
        'tx_packets',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
