<?php
declare(strict_types=1);

namespace App\Providers;

use App\Forecasts\Contracts\ForecastProvider;
use App\Forecasts\Integrations\OpenMeteoForecastProvider;
use Illuminate\Support\ServiceProvider;

class ForecastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenMeteoForecastProvider::class, function () {
            return new OpenMeteoForecastProvider(
                config('forecasts.open_meteo.base_url'),
            );
        });

        $this->app->alias(OpenMeteoForecastProvider::class, ForecastProvider::class);
    }
}
