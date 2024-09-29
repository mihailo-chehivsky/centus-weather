<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Forecast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Forecast>
 */
class ForecastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = City::query()->first() ?? City::factory()->create();

        return [
            'city_id' => $city->id,
            'hour'    => Carbon::make(fake()->dateTime())->startOfHour(),
            'values'  => new Forecast\Values(fake()->randomFloat(), fake()->randomFloat()),
        ];
    }
}
