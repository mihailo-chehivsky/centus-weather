<?php

namespace Tests\Feature;

use App\Forecasts\Contracts\ForecastProvider;
use App\Forecasts\Models\ForecastHour;
use App\Jobs\NotifyUsers;
use App\Jobs\UpdateCityForecasts;
use App\Models\City;
use App\Models\Forecast;
use App\Models\User;
use App\Services\ForecastService;
use Carbon\Carbon;
use Generator;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Throwable;

class ForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    private ?ForecastService $service              = null;
    private ?MockObject      $forecastProviderMock = null;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->forecastProviderMock = $this->createMock(ForecastProvider::class);
        $this->instance(ForecastProvider::class, $this->forecastProviderMock);

        $this->service = app(ForecastService::class);
    }

    protected function refreshTestDatabase(): void
    {
        User::query()->delete();
        Forecast::query()->delete();
        City::query()->delete();
    }

    public function test_update_successfully(): void
    {
        $city1 = City::factory()->create();
        $city2 = City::factory()->create();
        City::factory(3)->create();

        User::factory(2)->create(['city_id' => $city1->id]);
        User::factory(5)->create(['city_id' => $city2->id]);
        User::factory(10)->create();

        Queue::fake();

        $this->service->update();

        Queue::assertPushed(UpdateCityForecasts::class, 2);
    }

    /**
     * @dataProvider updateCityDataProvider
     *
     * @throws Throwable
     * @throws GuzzleException
     */
    public function test_update_city_successfully(array $providerData, int $countResults): void
    {
        $city = City::factory()->create();
        City::factory(3)->create();

        $this->forecastProviderMock->method('getByLocation')->willReturn($providerData);

        Queue::fake();

        $this->service->updateCity($city->id, $city->latitude, $city->longitude);

        Queue::assertPushed(NotifyUsers::class, $countResults);
    }

    public static function updateCityDataProvider(): Generator
    {
        yield 'base' => [
            'providerData' => [
                new ForecastHour(Carbon::now()->startOfHour(), fake()->randomFloat(), fake()->randomFloat()),
                new ForecastHour(Carbon::now()->addHour()->startOfHour(), fake()->randomFloat(), fake()->randomFloat()),
                new ForecastHour(
                    Carbon::now()->addHours(2)->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(Carbon::now()->addHours(3)->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(Carbon::now()->addHours(4)->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(Carbon::now()->addHours(5)->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(
                    Carbon::now()->addWeeks()->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(Carbon::now()->addWeeks(2)->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(
                    Carbon::now()->addWeeks(2)->addHour()->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
                new ForecastHour(
                    Carbon::now()->addMonth()->startOfHour(),
                    fake()->randomFloat(),
                    fake()->randomFloat(),
                ),
            ],
            'countResults'         => 7,
        ];

        yield 'old data' => [
            'providerData' => [
                new ForecastHour(Carbon::now()->subHour()->startOfHour(), fake()->randomFloat(), fake()->randomFloat()),
                new ForecastHour(Carbon::now()->startOfHour(), fake()->randomFloat(), fake()->randomFloat()),
                new ForecastHour(Carbon::now()->addHour()->startOfHour(), fake()->randomFloat(), fake()->randomFloat()),
            ],
            'countResults'         => 2,
        ];
    }
}
