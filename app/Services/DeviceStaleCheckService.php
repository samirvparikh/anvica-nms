<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceDowntimeEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DeviceStaleCheckService
{
    public function __construct(
        protected AlertService $alertService,
        protected DeviceDowntimeService $downtimeService,
    ) {}

    /**
     * Mark devices as down and raise alerts when no API data was received recently.
     *
     * @return array{checked: int, marked_down: int, alerts_raised: int}
     */
    public function check(?int $staleAfterMinutes = null): array
    {
        $staleAfterMinutes = $staleAfterMinutes ?? (int) config('monitoring.stale_after_minutes', 5);
        $cutoff = Carbon::now()->subMinutes(max(1, $staleAfterMinutes));

        $staleDevices = $this->staleDevices($cutoff);
        $markedDown = 0;
        $alertsRaised = 0;

        foreach ($staleDevices as $device) {
            $previousHealth = $device->health_status;
            $wasAlreadyDown = strcasecmp((string) $previousHealth, Device::HEALTH_DOWN) === 0;

            if (! $wasAlreadyDown) {
                $device->update(['health_status' => Device::HEALTH_DOWN]);
                $markedDown++;

                $this->downtimeService->syncFromHealthChange(
                    $device->fresh(),
                    $previousHealth,
                    Device::HEALTH_DOWN,
                    DeviceDowntimeEvent::SOURCE_PUSH,
                );
            } else {
                $this->downtimeService->openEvent(
                    $device,
                    DeviceDowntimeEvent::SOURCE_PUSH,
                    'No API data received',
                    $device->last_seen,
                );
            }

            $hadOpenAlert = $device->alerts()
                ->where('status', \App\Models\Alert::STATUS_OPEN)
                ->where('alarm_type', \App\Models\Alert::ALARM_DEVICE_DOWN)
                ->exists();

            $this->alertService->raiseOfflineAlert(
                $device->fresh(),
                $this->offlineMessage($device, $staleAfterMinutes),
            );

            if (! $hadOpenAlert) {
                $alertsRaised++;
            }
        }

        return [
            'checked' => $staleDevices->count(),
            'marked_down' => $markedDown,
            'alerts_raised' => $alertsRaised,
        ];
    }

    /**
     * @return Collection<int, Device>
     */
    protected function staleDevices(Carbon $cutoff): Collection
    {
        return Device::query()
            ->whereNotNull('last_seen')
            ->where('last_seen', '<', $cutoff)
            ->orderBy('id')
            ->get();
    }

    protected function offlineMessage(Device $device, int $staleAfterMinutes): string
    {
        return sprintf(
            'Device Shutdown: %s has not sent data to /api/device/data since %s (>%d min).',
            $device->name,
            $device->last_seen?->format('Y-m-d H:i:s') ?? 'unknown',
            $staleAfterMinutes,
        );
    }
}
