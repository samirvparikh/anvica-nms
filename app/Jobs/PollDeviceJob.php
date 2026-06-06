<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\MonitoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PollDeviceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Device $device,
    ) {}

    public function handle(MonitoringService $monitoringService): void
    {
        $monitoringService->poll($this->device);
    }
}
