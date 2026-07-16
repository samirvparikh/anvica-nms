<?php

namespace App\Console\Commands;

use App\Services\AlertToAlarmConverter;
use Illuminate\Console\Command;

class ConvertUnacknowledgedAlertsToAlarms extends Command
{
    protected $signature = 'alerts:convert-to-alarms';

    protected $description = 'Convert open alerts not acknowledged within 15 minutes into alarms';

    public function handle(AlertToAlarmConverter $converter): int
    {
        $count = $converter->convertExpiredAlerts();

        $this->info("Converted {$count} alert(s) to alarm(s).");

        return self::SUCCESS;
    }
}
