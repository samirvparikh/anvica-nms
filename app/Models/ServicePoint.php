<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePoint extends Model
{
    protected $fillable = ['service_id', 'point', 'method'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
