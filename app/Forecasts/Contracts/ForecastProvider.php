<?php
declare(strict_types=1);

namespace App\Forecasts\Contracts;

use App\Forecasts\Models\ForecastHour;

interface ForecastProvider
{
    /**
     * @return ForecastHour[]
     */
    public function getByLocation(float $latitude, float $longitude): array;
}
