<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $fillable = [
        'device_name',
        'message',
        'severity',
        'status',
    ];
}
