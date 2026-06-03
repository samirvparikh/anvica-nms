<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    protected $table = 'api_request_logs';

    protected $fillable = [
        'url',
        'method',
        'ip_address',
        'user_agent',
        'referer',
        'request_data',
        'headers',
    ];

    protected $casts = [
        'request_data' => 'array',
        'headers' => 'array',
    ];
}
