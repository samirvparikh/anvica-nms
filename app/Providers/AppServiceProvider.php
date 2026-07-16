<?php

namespace App\Providers;

use App\Models\Alarm;
use App\Models\Alert;
use App\Models\User;
use App\Repositories\AlertRepository;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        MailConfigService::apply();

        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            $openAlerts = $user ? app(AlertRepository::class)->openCount($user) : 0;
            $openAlarms = Alarm::where('status', 'Open')->count();

            $headerAlertNotifications = $user ? app(AlertRepository::class)->scopedQuery($user)
                ->where('status', Alert::STATUS_OPEN)
                ->with('device')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'device_name' => $item->device?->name ?? 'Unknown',
                        'message' => $item->message,
                        'severity' => strtolower($item->severity),
                        'created_at' => $item->created_at,
                        'url' => route('alerts.index'),
                    ];
                }) : collect();

            $headerAlarmNotifications = Alarm::where('status', 'Open')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'device_name' => $item->device_name,
                        'message' => $item->message,
                        'severity' => strtolower($item->severity),
                        'created_at' => $item->created_at,
                        'url' => route('alarms.index'),
                    ];
                });

            $view->with([
                'activeAlertsCount' => $openAlerts,
                'headerAlertNotifications' => $headerAlertNotifications,
                'activeAlarmsCount' => $openAlarms,
                'headerAlarmNotifications' => $headerAlarmNotifications,
                // Backward-compatible aliases used by older blade snippets.
                'headerNotifications' => $headerAlertNotifications,
            ]);

            if ($user && $user->isAdmin() && (request()->is('/') || request()->is('dashboard*'))) {
                $view->with('dashboardUsers', User::query()->customers()->orderBy('name')->get(['id', 'name']));
            }
        });
    }
}
