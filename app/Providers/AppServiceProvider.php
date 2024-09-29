<?php

namespace App\Providers;

use App\Jobs\UpdateForecasts;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schedule::job(new UpdateForecasts())->hourly();
    }
}
