<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\ServicePoint;
use App\Repositories\AlertRepository;

class AlertService
{
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
                'severity' => $severity,
                'message' => sprintf('%s on %s: %.2f%%', $rule['message'], $device->name, $value),
                'status' => Alert::STATUS_OPEN,
            ]);
        }

        if ($device->status === 'Down') {
            $this->raiseOfflineAlert($device);
        }
    }

    public function raiseOfflineAlert(Device $device): void
    {
        $exists = Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where('message', 'like', '%Device Offline%')
            ->exists();

        if ($exists) {
            return;
        }

        $this->alertRepository->create([
            'device_id' => $device->id,
            'severity' => Alert::SEVERITY_CRITICAL,
            'message' => 'Device Offline: ' . $device->name . ' is unreachable',
            'status' => Alert::STATUS_OPEN,
        ]);
    }

    protected function closeAlertsForMetric(Device $device, string $slug): void
    {
        Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where('message', 'like', '%' . strtoupper($slug) . '%')
            ->update(['status' => Alert::STATUS_CLOSED]);
    }
}
