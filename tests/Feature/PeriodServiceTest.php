<?php

namespace Tests\Feature;

use App\Services\PeriodService;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;
use Tests\TestCase;

class PeriodServiceTest extends TestCase
{
    private ?PeriodService $service = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PeriodService::class);
    }

    /**
     * @throws Exception
     */
    public function test_create_periods_successfully(): void
    {
        $now = Carbon::now()->startOfHour();

        $expected = [
            new DatePeriod($now->clone(), new DateInterval('PT1H'), $now->clone()->add(new DateInterval('PT1H'))),
            new DatePeriod($now->clone(), new DateInterval('P1D'), $now->clone()->add(new DateInterval('P1D'))),
            new DatePeriod($now->clone(), new DateInterval('P7D'), $now->clone()->add(new DateInterval('P7D'))),
        ];

        $actual = $this->service->createPeriods();

        $this->assertEquals($expected, $actual);
    }
}
