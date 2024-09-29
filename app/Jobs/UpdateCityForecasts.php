<?php

namespace App\Jobs;

use App\Services\ForecastService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class UpdateCityForecasts implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int   $cityId,
        public readonly float $latitude,
        public readonly float $longitude,
    ) {
    }

    /**
     * Execute the job.
     *
     * @throws GuzzleException
     * @throws Throwable
     */
    public function handle(ForecastService $forecastService): void
    {
        $forecastService->updateCity($this->cityId, $this->latitude, $this->longitude);
    }
}
