<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Forecast;
use App\Models\Forecast\Values;
use App\Models\User;
use App\Notifications\HarmfulWeather;
use Carbon\Carbon;
use DatePeriod;
use DateTimeInterface;
use Exception;

readonly class NotificationService
{
    /**
     * @throws Exception
     */
    public function notify(
        int               $forecastId,
        int               $cityId,
        DateTimeInterface $hour,
        Values            $values,
        DatePeriod        $period,
    ): void {
        if ($this->isNotified($forecastId, $cityId, $hour, $period)) {
            return;
        }

        $harmfulPrecipitation = config('app.harmful_weather.precipitation_per_hour');
        $harmfulUVIndex       = config('app.harmful_weather.uv_index');

        if ($values->precipitation < $harmfulPrecipitation && $values->uvIndex < $harmfulUVIndex) {
            return;
        }

        /** @var User[] $users */
        $users = User::query()->where('city_id', '=', $cityId)->cursor();

        foreach ($users as $user) {
            $user->notify(new HarmfulWeather($hour, $values));
        }
    }

    private function isNotified(int $forecastId, int $cityId, DateTimeInterface $hour, DatePeriod $period): bool
    {
        return Forecast::query()
            ->where('id', '<', $forecastId)
            ->where('city_id', '=', $cityId)
            ->where('hour', '=', $hour)
            ->where('created_at', '>', Carbon::now()->sub($period->interval))
            ->exists();
    }
}
