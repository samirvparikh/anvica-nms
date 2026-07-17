<?php

namespace App\Http\Controllers;

use App\Models\CronLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CronLogController extends Controller
{
    /**
     * Display a listing of cron/scheduler run logs (superadmin only).
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $date = $this->resolveDate($request->query('date'));

        $logs = CronLog::query()
            ->whereDate('started_at', $date->toDateString())
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('cron_logs.index', [
            'logs' => $logs,
            'date' => $date->toDateString(),
        ]);
    }

    private function resolveDate(?string $value): Carbon
    {
        if ($value) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable $e) {
                // Fall back to today on invalid input.
            }
        }

        return Carbon::today();
    }
}
