<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceWindow extends Model
{
    protected $fillable = [
        'title',
        'maintenance_id',
        'type',
        'category',
        'primary_device_id',
        'start_time',
        'end_time',
        'expected_downtime_minutes',
        'exclude_sla',
        'sla_impact',
        'sla_policy',
        'notify_before_minutes',
        'notification_recipients',
        'implementation_steps',
        'rollback_plan',
        'notify_users',
        'notification_method',
        'notification_message',
        'requested_by',
        'approved_noc_manager',
        'approved_it_head',
        'customer_approval',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'exclude_sla' => 'boolean',
            'notify_users' => 'boolean',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'primary_device_id');
    }
}
