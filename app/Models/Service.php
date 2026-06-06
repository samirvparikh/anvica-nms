<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = ['name', 'slug', 'icon', 'status'];

    public function points(): HasMany
    {
        return $this->hasMany(ServicePoint::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(DeviceVendor::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
