<?php

namespace App\Console\Commands;

use App\Models\CronLog;
use App\Services\DeviceStaleCheckService;
use Illuminate\Console\Command;
use Throwable;

class CheckStaleDevices extends Command
{
    protected $signature = 'devices:check-stale {--minutes= : Minutes without API data before marking device down}';

    protected $description = 'Mark devices down and create alerts when no /api/device/data has been received recently';

    public function handle(DeviceStaleCheckService $checker): int
    {
        $log = CronLog::start($this->getName());

        try {
            $minutes = $this->option('minutes');
            $result = $checker->check($minutes !== null ? (int) $minutes : null);

            $message = sprintf(
                'Checked %d stale device(s); marked %d down; raised %d new alert(s).',
                $result['checked'],
                $result['marked_down'],
                $result['alerts_raised'],
            );

            $this->info($message);
            $log->markSuccess($message, $result['checked']);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $log->markFailed($e->getMessage(), self::FAILURE);

            return self::FAILURE;
        }
    }
}
