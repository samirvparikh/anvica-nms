<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'alarm_type' => fake()->randomElement([
                Alert::ALARM_DEVICE_DOWN,
                Alert::ALARM_HIGH_CPU,
                Alert::ALARM_HIGH_RAM,
                Alert::ALARM_DISK_USAGE,
                Alert::ALARM_TEMPERATURE,
            ]),
            'severity' => fake()->randomElement([
                Alert::SEVERITY_CRITICAL,
                Alert::SEVERITY_WARNING,
                Alert::SEVERITY_INFO,
            ]),
            'message' => fake()->sentence(),
            'status' => Alert::STATUS_OPEN,
            'started_at' => now()->subMinutes(fake()->numberBetween(5, 5000)),
        ];
    }
}
