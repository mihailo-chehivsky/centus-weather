<?php
declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;

readonly class PeriodService
{
    private const PERIODS = ['PT1H', 'P1D', 'P7D'];

    /**
     * @return DatePeriod[]
     * @throws Exception
     */
    public function createPeriods(): array
    {
        $now = Carbon::now()->startOfHour();

        $periods = [];

        foreach (self::PERIODS as $duration) {
            $interval  = new DateInterval($duration);
            $periods[] = new DatePeriod($now->clone(), $interval, $now->clone()->add($interval));
        }

        return $periods;
    }
}
