<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\ServicePoint;
use App\Repositories\AlertRepository;
use Carbon\Carbon;

class AlertService
{
    /** @var array<string, string> */
    protected array $metricAlarmTypes = [
        'cpu' => Alert::ALARM_HIGH_CPU,
        'ram' => Alert::ALARM_HIGH_RAM,
        'disk' => Alert::ALARM_DISK_USAGE,
        'temperature' => Alert::ALARM_TEMPERATURE,
    ];

    public function __construct(
        protected AlertRepository $alertRepository,
    ) {}

    /**
     * @param  array<string, float|int>  $metrics
     */
    public function evaluateDevice(Device $device, array $metrics): void
    {
        $rules = [
            'cpu' => ['warning' => 80, 'critical' => 95, 'message' => 'CPU usage exceeded threshold'],
            'ram' => ['warning' => 90, 'critical' => 95, 'message' => 'RAM usage exceeded threshold'],
            'disk' => ['warning' => 90, 'critical' => 95, 'message' => 'Disk usage exceeded threshold'],
            'temperature' => ['warning' => 70, 'critical' => 85, 'message' => 'Temperature exceeded threshold'],
        ];

        foreach ($rules as $slug => $rule) {
            $value = (float) ($metrics[$slug] ?? 0);
            if ($value <= 0) {
                continue;
            }

            $severity = null;
            if ($value >= $rule['critical']) {
                $severity = Alert::SEVERITY_CRITICAL;
            } elseif ($value >= $rule['warning']) {
                $severity = Alert::SEVERITY_WARNING;
            }

            if (! $severity) {
                $this->closeAlertsForMetric($device, $slug);

                continue;
            }

            $servicePoint = ServicePoint::where('service_id', $device->service_id)
                ->where('slug', $slug)
                ->first();

            $existing = Alert::where('device_id', $device->id)
                ->where('status', Alert::STATUS_OPEN)
                ->where('message', 'like', '%' . strtoupper($slug) . '%')
                ->first();

            if ($existing) {
                $existing->update(['severity' => $severity]);

                continue;
            }

            $this->alertRepository->create([
                'device_id' => $device->id,
                'service_point_id' => $servicePoint?->id,
                'alarm_type' => $this->metricAlarmTypes[$slug] ?? Alert::ALARM_THRESHOLD,
                'severity' => $severity,
                'message' => sprintf('%s on %s: %.2f%%', $rule['message'], $device->name, $value),
                'status' => Alert::STATUS_OPEN,
                'started_at' => now(),
            ]);
        }

        if ($device->health_status === Device::HEALTH_DOWN) {
            $this->raiseOfflineAlert($device);
        } else {
            $this->closeOfflineAlerts($device);
        }
    }

    public function raiseOfflineAlert(Device $device): void
    {
        $exists = Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where('alarm_type', Alert::ALARM_DEVICE_DOWN)
            ->exists();

        if ($exists) {
            return;
        }

        $this->alertRepository->create([
            'device_id' => $device->id,
            'alarm_type' => Alert::ALARM_DEVICE_DOWN,
            'severity' => Alert::SEVERITY_CRITICAL,
            'message' => 'Device Offline: ' . $device->name . ' is unreachable',
            'status' => Alert::STATUS_OPEN,
            'started_at' => now(),
        ]);
    }

    public function closeOfflineAlerts(Device $device): void
    {
        Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where(function ($query) {
                $query->where('alarm_type', Alert::ALARM_DEVICE_DOWN)
                    ->orWhere('message', 'like', '%Device Offline%');
            })
            ->get()
            ->each(fn (Alert $alert) => $this->closeAlert($alert));
    }

    protected function closeAlertsForMetric(Device $device, string $slug): void
    {
        Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where('message', 'like', '%' . strtoupper($slug) . '%')
            ->get()
            ->each(fn (Alert $alert) => $this->closeAlert($alert));
    }

    public function closeAlert(Alert $alert, ?Carbon $resolvedAt = null): void
    {
        if ($alert->status === Alert::STATUS_CLOSED) {
            return;
        }

        $resolvedAt = $resolvedAt ?? now();
        $startedAt = $alert->started_at ?? $alert->created_at;

        $alert->update([
            'status' => Alert::STATUS_CLOSED,
            'resolved_at' => $resolvedAt,
            'duration_seconds' => (int) $startedAt->diffInSeconds($resolvedAt),
        ]);
    }
}
