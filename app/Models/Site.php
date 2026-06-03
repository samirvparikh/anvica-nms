<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'name',
        'up_devices',
        'total_devices',
        'x_pos',
        'y_pos',
    ];
}
