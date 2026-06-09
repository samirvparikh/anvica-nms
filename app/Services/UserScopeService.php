<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceInterface;
use App\Models\DeviceMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserScopeService
{
    public function devicesQuery(User $user, ?int $customerId = null): Builder
    {
        if ($user->isAdmin()) {
            $query = Device::query();

            if ($customerId) {
                $query->where('user_id', $customerId);
            }

            return $query;
        }

        return Device::where('user_id', $user->id);
    }

    public function deviceIds(User $user, ?int $customerId = null): array
    {
        return $this->devicesQuery($user, $customerId)->pluck('id')->all();
    }

    public function canAccessDevice(User $user, Device $device): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $device->user_id === $user->id;
    }

    public function alertsQuery(User $user, ?int $customerId = null): Builder
    {
        $deviceIds = $this->deviceIds($user, $customerId);

        return Alert::query()->whereIn('device_id', $deviceIds ?: [-1]);
    }

    public function metricsQuery(User $user, ?int $customerId = null): Builder
    {
        $deviceIds = $this->deviceIds($user, $customerId);

        return DeviceMetric::query()->whereIn('device_id', $deviceIds ?: [-1]);
    }

    public function interfacesQuery(User $user, ?int $customerId = null): Builder
    {
        $deviceIds = $this->deviceIds($user, $customerId);

        return DeviceInterface::query()->whereIn('device_id', $deviceIds ?: [-1]);
    }

    /**
     * @return array<int, array<string, DeviceMetric>>
     */
    public function latestMetricsByDevice(User $user, ?int $customerId = null): array
    {
        $deviceIds = $this->deviceIds($user, $customerId);

        if (empty($deviceIds)) {
            return [];
        }

        $metrics = DeviceMetric::whereIn('device_id', $deviceIds)
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('device_id');

        $result = [];
        foreach ($metrics as $deviceId => $deviceMetrics) {
            $result[$deviceId] = $deviceMetrics
                ->groupBy('metric_slug')
                ->map(fn ($group) => $group->first())
                ->all();
        }

        return $result;
    }

    /**
     * Health from device_metrics within last 5 minutes.
     * Shows Ping_Status value when present; otherwise Up if any row exists, else Down.
     *
     * @return array<int, string>
     */
    public function deviceHealthByRecentMetrics(User $user, ?int $customerId = null, int $withinMinutes = 5): array
    {
        $deviceIds = $this->deviceIds($user, $customerId);

        if (empty($deviceIds)) {
            return [];
        }

        $recentMetrics = DeviceMetric::query()
            ->whereIn('device_id', $deviceIds)
            ->where('recorded_at', '>=', now()->subMinutes($withinMinutes))
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('device_id');

        $health = [];
        foreach ($deviceIds as $deviceId) {
            $metrics = $recentMetrics->get($deviceId);

            if (! $metrics || $metrics->isEmpty()) {
                $health[$deviceId] = 'Down';

                continue;
            }

            $latestBySlug = $metrics
                ->groupBy('metric_slug')
                ->map(fn ($group) => $group->first());

            $pingMetric = $latestBySlug->get('Ping_Status') ?? $latestBySlug->get('ping_status');

            if ($pingMetric) {
                $health[$deviceId] = $pingMetric->metric_text
                    ?? (strtoupper((string) $pingMetric->metric_value) === '1' || (float) $pingMetric->metric_value >= 1 ? 'UP' : 'DOWN');

                continue;
            }

            $health[$deviceId] = 'Up';
        }

        return $health;
    }
}
