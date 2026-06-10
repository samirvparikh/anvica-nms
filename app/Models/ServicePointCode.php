<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePointCode extends Model
{
    protected $fillable = [
        'vendor_id',
        'service_point_id',
        'name',
        'code',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(DeviceVendor::class, 'vendor_id');
    }

    public function servicePoint(): BelongsTo
    {
        return $this->belongsTo(ServicePoint::class);
    }
}
