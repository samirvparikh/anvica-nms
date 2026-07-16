<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $fillable = [
        'alert_id',
        'device_name',
        'message',
        'severity',
        'status',
    ];

    public function alert(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }
}
