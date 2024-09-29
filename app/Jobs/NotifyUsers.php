<?php

namespace App\Jobs;

use App\Models\Forecast\Values;
use App\Services\NotificationService;
use DatePeriod;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyUsers implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int               $forecastId,
        public readonly int               $cityId,
        public readonly DateTimeInterface $hour,
        public readonly Values            $values,
        public readonly DatePeriod        $period,
    ) {
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(NotificationService $notificationService): void
    {
        $notificationService->notify($this->forecastId, $this->cityId, $this->hour, $this->values, $this->period);
    }
}
