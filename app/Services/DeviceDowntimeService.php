<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceDowntimeEvent;
use Carbon\Carbon;

class DeviceDowntimeService
{
    public function syncFromHealthChange(
        Device $device,
        ?string $previousStatus,
        string $newStatus,
        string $source = DeviceDowntimeEvent::SOURCE_POLL,
    ): void {
        $previousStatus = $previousStatus ?? Device::HEALTH_UP;
        $wasDown = $this->isDownStatus($previousStatus);
        $isDown = $this->isDownStatus($newStatus);

        if (! $wasDown && $isDown) {
            $this->openEvent($device, $source);
        }

        if ($wasDown && ! $isDown) {
            $this->closeOpenEvent($device);
        }
    }

    public function openEvent(Device $device, string $source, ?string $reason = null, ?Carbon $downAt = null): DeviceDowntimeEvent
    {
        $existing = DeviceDowntimeEvent::query()
            ->where('device_id', $device->id)
            ->whereNull('up_at')
            ->latest('down_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DeviceDowntimeEvent::create([
            'device_id' => $device->id,
            'down_at' => $downAt ?? now(),
            'reason' => $reason ?? $this->inferReason($device),
            'source' => $source,
        ]);
    }

    public function closeOpenEvent(Device $device, ?Carbon $upAt = null): ?DeviceDowntimeEvent
    {
        $event = DeviceDowntimeEvent::query()
            ->where('device_id', $device->id)
            ->whereNull('up_at')
            ->latest('down_at')
            ->first();

        if (! $event) {
            return null;
        }

        $upAt = $upAt ?? now();
        $event->update([
            'up_at' => $upAt,
            'duration_seconds' => (int) $event->down_at->diffInSeconds($upAt),
        ]);

        return $event->fresh();
    }

    protected function isDownStatus(string $status): bool
    {
        return strcasecmp($status, Device::HEALTH_DOWN) === 0;
    }

    protected function inferReason(Device $device): string
    {
        if ($device->health_status === Device::HEALTH_DOWN) {
            return 'Device Not Responding';
        }

        return 'Health Status Degraded';
    }
}
