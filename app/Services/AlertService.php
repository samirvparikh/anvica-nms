<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\ServicePoint;
use App\Repositories\AlertRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
     * Evaluate normalized poll metrics (cpu, ram, disk, temperature).
     *
     * @param  array<string, float|int>  $metrics
     */
    public function evaluateDevice(Device $device, array $metrics): void
    {
        $readings = [];

        foreach ($metrics as $slug => $value) {
            $readings[] = [
                'slug' => (string) $slug,
                'value' => (float) $value,
                'text' => null,
            ];
        }

        $this->evaluateDeviceFromReadings($device, $readings);
    }

    /**
     * Evaluate API / flat payload readings against service point thresholds.
     *
     * @param  list<array{slug: string, value: float|int, text: ?string}>  $readings
     */
    public function evaluateDeviceFromReadings(Device $device, array $readings): void
    {
        if ($device->service_id) {
            $servicePoints = ServicePoint::query()
                ->where('service_id', $device->service_id)
                ->where('status', ServicePoint::STATUS_ACTIVE)
                ->get();

            $this->evaluateServicePoints($device, $servicePoints, $readings);
        } else {
            $this->evaluateFallbackMetrics($device, $readings);
        }

        $this->evaluateDeviceHealth($device);
    }

    /**
     * @param  Collection<int, ServicePoint>  $servicePoints
     * @param  list<array{slug: string, value: float|int, text: ?string}>  $readings
     */
    protected function evaluateServicePoints(Device $device, Collection $servicePoints, array $readings): void
    {
        foreach ($servicePoints as $point) {
            if ($point->warning_threshold === null && $point->critical_threshold === null) {
                continue;
            }

            if ($this->isStatusServicePoint($point)) {
                continue;
            }

            $reading = $this->findReadingForServicePoint($point, $readings);
            if ($reading === null) {
                continue;
            }

            $value = $this->extractNumericValue($reading, $point);
            if ($value === null) {
                continue;
            }

            $severity = $this->resolveSeverity($value, $point);

            if ($severity === null) {
                $this->closeAlertsForServicePoint($device, $point);

                continue;
            }

            $this->raiseOrUpdateMetricAlert($device, $point, $severity, $value);
        }
    }

    /**
     * @param  list<array{slug: string, value: float|int, text: ?string}>  $readings
     */
    protected function evaluateFallbackMetrics(Device $device, array $readings): void
    {
        $rules = [
            'cpu' => ['warning' => 80, 'critical' => 95, 'message' => 'CPU usage exceeded threshold'],
            'ram' => ['warning' => 90, 'critical' => 95, 'message' => 'RAM usage exceeded threshold'],
            'disk' => ['warning' => 90, 'critical' => 95, 'message' => 'Disk usage exceeded threshold'],
            'temperature' => ['warning' => 70, 'critical' => 85, 'message' => 'Temperature exceeded threshold'],
        ];

        $indexed = [];
        foreach ($readings as $reading) {
            $indexed[strtolower($reading['slug'])] = $reading;
        }

        foreach ($rules as $slug => $rule) {
            $reading = $indexed[$slug] ?? null;
            if ($reading === null) {
                continue;
            }

            $value = (float) $reading['value'];
            if ($value <= 0) {
                continue;
            }

            $severity = null;
            if ($value >= $rule['critical']) {
                $severity = Alert::SEVERITY_CRITICAL;
            } elseif ($value >= $rule['warning']) {
                $severity = Alert::SEVERITY_WARNING;
            }

            if ($severity === null) {
                $this->closeAlertsForMetricSlug($device, $slug);

                continue;
            }

            $existing = Alert::query()
                ->where('device_id', $device->id)
                ->where('status', Alert::STATUS_OPEN)
                ->where('message', 'like', '%' . strtoupper($slug) . '%')
                ->first();

            if ($existing) {
                $existing->update(['severity' => $severity]);

                continue;
            }

            $this->alertRepository->create([
                'device_id' => $device->id,
                'alarm_type' => $this->metricAlarmTypes[$slug] ?? Alert::ALARM_THRESHOLD,
                'severity' => $severity,
                'message' => sprintf('%s on %s: %.2f', $rule['message'], $device->name, $value),
                'status' => Alert::STATUS_OPEN,
                'started_at' => now(),
            ]);
        }
    }

    protected function evaluateDeviceHealth(Device $device): void
    {
        if ($device->health_status === Device::HEALTH_DOWN) {
            $this->raiseOfflineAlert($device);
        } else {
            $this->closeOfflineAlerts($device);
        }
    }

    protected function raiseOrUpdateMetricAlert(Device $device, ServicePoint $point, string $severity, float $value): void
    {
        $existing = Alert::query()
            ->where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where(function ($query) use ($point) {
                $query->where('service_point_id', $point->id)
                    ->orWhere('message', 'like', '%' . $point->name . '%');
            })
            ->first();

        $unit = $point->unit ? ' ' . $point->unit : '';
        $message = sprintf(
            '%s threshold exceeded on %s: %.2f%s (warning: %s, critical: %s)',
            $point->name,
            $device->name,
            $value,
            $unit,
            $point->warning_threshold ?? '—',
            $point->critical_threshold ?? '—',
        );

        if ($existing) {
            $existing->update([
                'service_point_id' => $point->id,
                'severity' => $severity,
                'message' => $message,
                'alarm_type' => $this->inferAlarmType($point),
            ]);

            return;
        }

        $this->alertRepository->create([
            'device_id' => $device->id,
            'service_point_id' => $point->id,
            'alarm_type' => $this->inferAlarmType($point),
            'severity' => $severity,
            'message' => $message,
            'status' => Alert::STATUS_OPEN,
            'started_at' => now(),
        ]);
    }

    protected function inferAlarmType(ServicePoint $point): string
    {
        $slug = strtolower(str_replace([' ', '-'], '_', $point->slug));

        if (str_contains($slug, 'cpu')) {
            return Alert::ALARM_HIGH_CPU;
        }

        if (str_contains($slug, 'ram') || str_contains($slug, 'memory')) {
            return Alert::ALARM_HIGH_RAM;
        }

        if (str_contains($slug, 'disk') || str_contains($slug, 'storage')) {
            return Alert::ALARM_DISK_USAGE;
        }

        if (str_contains($slug, 'temp')) {
            return Alert::ALARM_TEMPERATURE;
        }

        return Alert::ALARM_THRESHOLD;
    }

    protected function resolveSeverity(float $value, ServicePoint $point): ?string
    {
        $warning = $point->warning_threshold;
        $critical = $point->critical_threshold;

        if ($warning === null && $critical === null) {
            return null;
        }

        $lowerIsWorse = $warning !== null
            && $critical !== null
            && (float) $critical < (float) $warning;

        if ($lowerIsWorse) {
            if ($critical !== null && $value <= (float) $critical) {
                return Alert::SEVERITY_CRITICAL;
            }

            if ($warning !== null && $value <= (float) $warning) {
                return Alert::SEVERITY_WARNING;
            }

            return null;
        }

        if ($critical !== null && $value >= (float) $critical) {
            return Alert::SEVERITY_CRITICAL;
        }

        if ($warning !== null && $value >= (float) $warning) {
            return Alert::SEVERITY_WARNING;
        }

        return null;
    }

    /**
     * @param  list<array{slug: string, value: float|int, text: ?string}>  $readings
     * @return array{slug: string, value: float|int, text: ?string}|null
     */
    protected function findReadingForServicePoint(ServicePoint $point, array $readings): ?array
    {
        $pointVariants = $this->slugVariants($point->slug);

        foreach ($readings as $reading) {
            if (array_intersect($pointVariants, $this->slugVariants($reading['slug']))) {
                return $reading;
            }
        }

        return null;
    }

    /**
     * @param  array{slug: string, value: float|int, text: ?string}  $reading
     */
    protected function extractNumericValue(array $reading, ServicePoint $point): ?float
    {
        $value = (float) $reading['value'];
        $text = trim((string) ($reading['text'] ?? ''));

        if ($value <= 0 && $text === '') {
            return null;
        }

        if ($this->isPercentageUnit($point->unit) && $value > 100) {
            return null;
        }

        return $value;
    }

    protected function isPercentageUnit(?string $unit): bool
    {
        return in_array(trim((string) $unit), ['%', 'percent', 'pct'], true);
    }

    protected function isStatusServicePoint(ServicePoint $point): bool
    {
        $slug = strtolower(str_replace([' ', '-'], '_', $point->slug));

        return str_contains($slug, 'ping')
            || str_contains($slug, 'status')
            || str_contains($slug, 'online');
    }

    /**
     * @return list<string>
     */
    protected function slugVariants(string $slug): array
    {
        $normalized = strtolower(str_replace([' ', '-'], '_', trim($slug)));

        return array_values(array_unique(array_filter([
            $slug,
            strtolower($slug),
            $normalized,
            Str::slug($slug, '_'),
            str_replace('_', '', $normalized),
        ])));
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

    protected function closeAlertsForServicePoint(Device $device, ServicePoint $point): void
    {
        Alert::where('device_id', $device->id)
            ->where('status', Alert::STATUS_OPEN)
            ->where(function ($query) use ($point) {
                $query->where('service_point_id', $point->id)
                    ->orWhere('message', 'like', '%' . $point->name . '%');
            })
            ->get()
            ->each(fn (Alert $alert) => $this->closeAlert($alert));
    }

    protected function closeAlertsForMetricSlug(Device $device, string $slug): void
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
