<?php

namespace App\Console\Commands;

use App\Models\CronLog;
use App\Services\AlertToAlarmConverter;
use Illuminate\Console\Command;
use Throwable;

class ConvertUnacknowledgedAlertsToAlarms extends Command
{
    protected $signature = 'alerts:convert-to-alarms';

    protected $description = 'Convert open alerts not acknowledged within 15 minutes into alarms';

    public function handle(AlertToAlarmConverter $converter): int
    {
        $log = CronLog::start($this->getName());

        try {
            $count = $converter->convertExpiredAlerts();

            $message = "Converted {$count} alert(s) to alarm(s).";
            $this->info($message);
            $log->markSuccess($message, $count);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $log->markFailed($e->getMessage(), self::FAILURE);

            return self::FAILURE;
        }
    }
}
