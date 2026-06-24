<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaBreach extends Model
{
    protected $fillable = [
        'ticket_id',
        'type',
        'breached_at',
    ];

    protected function casts(): array
    {
        return [
            'breached_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
