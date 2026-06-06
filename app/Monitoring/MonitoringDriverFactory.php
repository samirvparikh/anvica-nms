<?php

namespace App\Monitoring;

use App\Models\Device;
use App\Monitoring\Contracts\MonitoringDriverInterface;
use App\Monitoring\Drivers\CiscoDriver;
use App\Monitoring\Drivers\FortigateDriver;
use App\Monitoring\Drivers\GenericDriver;
use App\Monitoring\Drivers\LinuxServerDriver;
use App\Monitoring\Drivers\MikroTikDriver;
use App\Monitoring\Drivers\WindowsServerDriver;

class MonitoringDriverFactory
{
    /** @var array<string, class-string<MonitoringDriverInterface>> */
    protected array $drivers = [
        'mikrotik' => MikroTikDriver::class,
        'cisco' => CiscoDriver::class,
        'juniper' => CiscoDriver::class,
        'huawei' => CiscoDriver::class,
        'ubiquiti' => MikroTikDriver::class,
        'fortigate' => FortigateDriver::class,
        'sophos' => FortigateDriver::class,
        'sonicwall' => FortigateDriver::class,
        'palo-alto' => FortigateDriver::class,
        'windows-server' => WindowsServerDriver::class,
        'linux-server' => LinuxServerDriver::class,
        'hikvision' => GenericDriver::class,
        'dahua' => GenericDriver::class,
        'cp-plus' => GenericDriver::class,
        'apc' => GenericDriver::class,
        'numeric' => GenericDriver::class,
        'vertiv' => GenericDriver::class,
        'tp-link' => GenericDriver::class,
        'aruba' => CiscoDriver::class,
        'd-link' => GenericDriver::class,
        'hp' => CiscoDriver::class,
    ];

    public function make(Device $device): MonitoringDriverInterface
    {
        $slug = $device->driverSlug() ?? 'generic';
        $class = $this->drivers[$slug] ?? GenericDriver::class;

        return app($class);
    }
}
