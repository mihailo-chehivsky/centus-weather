<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Forecast;
use App\Models\Forecast\Values;
use App\Models\User;
use App\Notifications\HarmfulWeather;
use App\Services\NotificationService;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ?NotificationService $service = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(NotificationService::class);
    }

    protected function refreshTestDatabase(): void
    {
        User::query()->delete();
        Forecast::query()->delete();
        City::query()->delete();
    }

    /**
     * @dataProvider notifyDataProvider
     *
     * @throws Exception
     */
    public function test_notify_successfully(array $oldData, array $data, DatePeriod $period, bool $withSending): void
    {
        $city1 = City::factory()->create();
        $city2 = City::factory()->create();
        City::factory(3)->create();

        User::factory(2)->create(['city_id' => $city1->id]);
        User::factory(5)->create(['city_id' => $city2->id]);
        User::factory(10)->create();

        foreach ($oldData as $item) {
            Forecast::factory()->create(array_merge($item, ['city_id' => $city1->id]));
        }
        $forecast = Forecast::factory()->create(array_merge($data, ['city_id' => $city1->id]));
        Forecast::factory(20)->create(['city_id' => $city2->id]);

        Notification::fake();

        $this->service->notify($forecast->id, $forecast->city_id, $forecast->hour, $forecast->values, $period);

        Notification::assertSentTimes(HarmfulWeather::class, $withSending ? 2 : 0);
    }

    public static function notifyDataProvider(): Generator
    {
        $now = Carbon::now()->startOfHour();

        $periodHour = new DatePeriod($now->clone(), new DateInterval('PT1H'), $now->clone()->addHour());
        $periodDay  = new DatePeriod($now->clone(), new DateInterval('P1D'), $now->clone()->addDay());
        $periodWeek = new DatePeriod($now->clone(), new DateInterval('P7D'), $now->clone()->addWeek());

        yield 'hourly' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodHour,
            'withSending' => true,
        ];

        yield 'hourly not harmful' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(2, 3)],
            'period'      => $periodHour,
            'withSending' => false,
        ];

        yield 'hourly was notified' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHour(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodHour,
            'withSending' => false,
        ];

        yield 'daily' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodDay,
            'withSending' => true,
        ];

        yield 'daily not harmful' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(2, 3)],
            'period'      => $periodDay,
            'withSending' => false,
        ];

        yield 'daily was notified' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDay(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subHours(3),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodDay,
            'withSending' => false,
        ];

        yield 'weekly' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodWeek,
            'withSending' => true,
        ];

        yield 'weekly not harmful' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(2, 3)],
            'period'      => $periodWeek,
            'withSending' => false,
        ];

        yield 'weekly was notified' => [
            'oldData'     => [
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeeks(2),
                ],
                [
                    'hour'       => $now->clone()->subHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone()->addHour(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subWeek(),
                ],
                [
                    'hour'       => $now->clone(),
                    'values'     => new Values(fake()->randomFloat(), fake()->randomFloat()),
                    'created_at' => $now->clone()->subDays(3),
                ],
            ],
            'data'        => ['hour' => $now->clone(), 'values' => new Values(55, 12)],
            'period'      => $periodWeek,
            'withSending' => false,
        ];
    }
}
