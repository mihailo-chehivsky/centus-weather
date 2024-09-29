<?php
declare(strict_types=1);

namespace App\Services;

use App\Forecasts\Contracts\ForecastProvider;
use App\Jobs\NotifyUsers;
use App\Jobs\UpdateCityForecasts;
use App\Models\City;
use App\Models\Forecast;
use App\Models\Forecast\Values;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ForecastService
{
    public function __construct(private ForecastProvider $forecastProvider, private PeriodService $periodService)
    {
    }

    public function update(): void
    {
        /** @var City[] $cities */
        $cities = City::query()
            ->join('users', 'users.city_id', '=', 'cities.id')
            ->distinct()
            ->get('cities.*');

        foreach ($cities as $city) {
            UpdateCityForecasts::dispatch($city->id, $city->latitude, $city->longitude);
        }
    }

    /**
     * @throws Throwable
     * @throws GuzzleException
     */
    public function updateCity(int $cityId, float $latitude, float $longitude): void
    {
        $forecastHours = $this->forecastProvider->getByLocation($latitude, $longitude);
        if (empty($forecastHours)) {
            return;
        }

        DB::beginTransaction();
        try {
            $forecasts = [];

            foreach ($forecastHours as $forecastHour) {
                $forecast = new Forecast([
                    'city_id' => $cityId,
                    'hour'    => $forecastHour->hour,
                    'values'  => new Values($forecastHour->precipitation, $forecastHour->uvIndex),
                ]);
                $forecast->save();

                $forecasts[] = $forecast;
            }

            $this->notify($forecasts);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Forecast[] $forecasts
     *
     * @throws Exception
     */
    private function notify(array $forecasts): void
    {
        $periods = $this->periodService->createPeriods();

        foreach ($forecasts as $forecast) {
            foreach ($periods as $period) {
                if ($forecast->hour >= $period->start && $forecast->hour <= $period->end) {
                    NotifyUsers::dispatch(
                        $forecast->id,
                        $forecast->city_id,
                        $forecast->hour,
                        $forecast->values,
                        $period,
                    );
                    break;
                }
            }
        }
    }
}
