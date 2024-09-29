<?php

namespace App\Jobs;

use App\Services\ForecastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateForecasts implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(ForecastService $forecastService): void
    {
        $forecastService->update();
    }
}
