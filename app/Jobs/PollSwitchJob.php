<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\MonitoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PollSwitchJob implements ShouldQueue
{
    use Queueable;

    public function handle(MonitoringService $monitoringService): void
    {
        Device::with(['service', 'vendor'])
            ->whereHas('service', fn ($q) => $q->where('slug', 'switch'))
            ->each(fn (Device $device) => $monitoringService->poll($device));
    }
}
