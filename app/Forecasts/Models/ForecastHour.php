<?php
declare(strict_types=1);

namespace App\Forecasts\Models;

use DateTimeInterface;

readonly class ForecastHour
{
    public function __construct(public DateTimeInterface $hour, public float $precipitation, public float $uvIndex)
    {
    }
}
