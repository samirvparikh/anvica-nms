<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeviceVendor extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'logo',
        'status',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'vendor_id');
    }

    public function servicePointCodes(): HasMany
    {
        return $this->hasMany(ServicePointCode::class, 'vendor_id');
    }

    public function script(): HasOne
    {
        return $this->hasOne(VendorScript::class, 'vendor_id');
    }
}
