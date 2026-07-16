<?php

use App\Jobs\PollDeviceJob;
use App\Jobs\PollFirewallJob;
use App\Jobs\PollRouterJob;
use App\Jobs\PollSwitchJob;
use App\Models\Device;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PollRouterJob)->everyMinute();
Schedule::job(new PollSwitchJob)->everyMinute();
Schedule::job(new PollFirewallJob)->everyMinute();

Schedule::command('alerts:convert-to-alarms')->everyMinute();

Schedule::call(function () {
    Device::with(['service', 'vendor'])
        ->whereNotNull('service_id')
        ->whereHas('service', function ($query) {
            $query->whereNotIn('slug', ['router', 'switch', 'firewall']);
        })
        ->each(fn (Device $device) => PollDeviceJob::dispatch($device));
})->everyMinute();
