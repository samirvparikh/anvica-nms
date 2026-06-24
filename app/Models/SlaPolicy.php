<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'response_time_minutes',
        'resolution_time_minutes',
        'escalation_time_minutes',
        'max_tickets_per_day',
        'max_changes_per_week',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
