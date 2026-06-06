<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DeviceVendor;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'hostname' => fake()->domainWord(),
            'type' => 'Router',
            'device_type' => 'Router',
            'ip_address' => fake()->ipv4(),
            'location' => fake()->city(),
            'status' => fake()->randomElement(['Up', 'Warning', 'Down']),
            'snmp_version' => '2c',
            'snmp_port' => 161,
            'snmp_community' => 'public',
        ];
    }

    public function forService(Service $service, ?DeviceVendor $vendor = null): static
    {
        return $this->state(fn () => [
            'service_id' => $service->id,
            'vendor_id' => $vendor?->id,
            'type' => $service->name,
            'device_type' => $service->name,
        ]);
    }
}
