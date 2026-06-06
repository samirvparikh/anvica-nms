<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['name'];

    public function points(): HasMany
    {
        return $this->hasMany(ServicePoint::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
