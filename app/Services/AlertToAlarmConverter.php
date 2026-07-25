<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\Alert;
use App\Models\AlertActivity;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->whereNull('resolved_at')
            ->whereNull('converted_to_alarm_at')
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->each(function (Alert $alert) use (&$converted, $now) {
                if ($this->convertAlert($alert, null, 'Auto-converted after 15 minutes without acknowledgement.', $now)) {
                    $converted++;
                }
            });

        return $converted;
    }

    /**
     * Convert a single alert into an alarm and close the alert.
     */
    public function convertAlert(
        Alert $alert,
        ?User $user = null,
        ?string $remarks = null,
        ?Carbon $now = null
    ): bool {
        $now = $now ?? now();

        if ($alert->converted_to_alarm_at !== null) {
            return false;
        }

        return DB::transaction(function () use ($alert, $user, $remarks, $now) {
            $alert->loadMissing('device');

            if (! Alarm::where('alert_id', $alert->id)->exists()) {
                Alarm::create([
                    'alert_id' => $alert->id,
                    'device_name' => $alert->device?->name ?? $alert->device?->asset_name ?? 'Unknown',
                    'message' => $alert->message,
                    'severity' => $this->mapSeverity($alert->severity),
                    'status' => 'Open',
                ]);
            }

            $updates = [
                'converted_to_alarm_at' => $now,
                'status' => Alert::STATUS_CLOSED,
                'resolved_at' => $now,
                'duration_seconds' => (int) ($alert->started_at ?? $alert->created_at)->diffInSeconds($now),
            ];

            if ($alert->acknowledged_at === null) {
                $updates['acknowledged_at'] = $now;
                $updates['acknowledged_by'] = $user?->id;
            }

            $alert->update($updates);

            AlertActivity::create([
                'alert_id' => $alert->id,
                'user_id' => $user?->id,
                'action' => AlertActivity::ACTION_CONVERTED_TO_ALARM,
                'status' => 'alarm',
                'remarks' => $remarks,
            ]);

            return true;
        });
    }

    public function mapSeverity(string $severity): string
    {
        return strtolower($severity) === 'critical' ? 'Critical' : 'Warning';
    }
}
