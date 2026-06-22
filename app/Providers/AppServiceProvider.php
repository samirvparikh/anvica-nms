<?php

namespace App\Providers;

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
            $view->with('activeAlertsCount', $user
                ? app(AlertRepository::class)->openCount($user)
                : 0);
        });
    }
}
