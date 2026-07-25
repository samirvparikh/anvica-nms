<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertActivity extends Model
{
    public const ACTION_ACKNOWLEDGED = 'acknowledged';

    public const ACTION_CONVERTED_TO_ALARM = 'converted_to_alarm';

    public const ACTION_RESOLVED = 'resolved';

    public const ACTION_CLOSED = 'closed';

    public const ACTION_CREATED = 'created';

    protected $fillable = [
        'alert_id',
        'user_id',
        'action',
        'status',
        'remarks',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_ACKNOWLEDGED => 'Acknowledged',
            self::ACTION_CONVERTED_TO_ALARM => 'Converted to Alarm',
            self::ACTION_RESOLVED => 'Resolved',
            self::ACTION_CLOSED => 'Closed',
            self::ACTION_CREATED => 'Created',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
