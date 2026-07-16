<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\Alert;
use Illuminate\Support\Carbon;

class AlertToAlarmConverter
{
    public const UNACKNOWLEDGED_MINUTES = 15;

    /**
     * Convert open, unacknowledged alerts older than 15 minutes into alarms.
     *
     * @return int Number of alerts converted
     */
    public function convertExpiredAlerts(?Carbon $now = null): int
    {
        $now = $now ?? now();
        $cutoff = $now->copy()->subMinutes(self::UNACKNOWLEDGED_MINUTES);
        $converted = 0;

        Alert::query()
            ->with('device')
            ->where('status', Alert::STATUS_OPEN)
            ->whereNull('acknowledged_at')
            ->whereNull('converted_to_alarm_at')
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->each(function (Alert $alert) use (&$converted, $now) {
                if (Alarm::where('alert_id', $alert->id)->exists()) {
                    $alert->update(['converted_to_alarm_at' => $now]);

                    return;
                }

                Alarm::create([
                    'alert_id' => $alert->id,
                    'device_name' => $alert->device?->name ?? $alert->device?->asset_name ?? 'Unknown',
                    'message' => $alert->message,
                    'severity' => $this->mapSeverity($alert->severity),
                    'status' => 'Open',
                ]);

                $alert->update([
                    'converted_to_alarm_at' => $now,
                    'status' => Alert::STATUS_CLOSED,
                    'resolved_at' => $now,
                    'duration_seconds' => (int) ($alert->started_at ?? $alert->created_at)->diffInSeconds($now),
                ]);

                $converted++;
            });

        return $converted;
    }

    protected function mapSeverity(string $severity): string
    {
        return strtolower($severity) === 'critical' ? 'Critical' : 'Warning';
    }
}
