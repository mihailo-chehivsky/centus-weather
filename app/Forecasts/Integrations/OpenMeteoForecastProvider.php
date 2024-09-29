<?php
declare(strict_types=1);

namespace App\Forecasts\Integrations;

use App\Forecasts\Contracts\ForecastProvider;
use App\Forecasts\Models\ForecastHour;
use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

readonly class OpenMeteoForecastProvider implements ForecastProvider
{
    public function __construct(private string $baseUrl)
    {
    }

    /**
     * @return ForecastHour[]
     * @throws GuzzleException
     */
    public function getByLocation(float $latitude, float $longitude): array
    {
        $client = new Client(['base_uri' => $this->baseUrl]);

        $response = $client->get('/v1/forecast', [
            'query' => [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'hourly'    => 'precipitation',
                'daily'     => 'uv_index_max',
                'timezone'  => 'GMT',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $mapped = [];

        foreach ($data['hourly']['time'] as $index => $time) {
            $mapped[$time]['precipitation'] = $data['hourly']['precipitation'][$index];
        }

        foreach ($data['daily']['time'] as $index => $date) {
            for ($i = 0; $i < 24; $i++) {
                $time = sprintf('%sT%s:00', $date, str_pad((string)$i, 2, '0', STR_PAD_LEFT));

                $mapped[$time]['uv_index'] = $data['daily']['uv_index_max'][$index];
            }
        }

        $forecastHours = [];
        foreach ($mapped as $time => $data) {
            $hour          = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $time);
            $precipitation = $data['precipitation'];
            $uvIndex       = $data['uv_index'];

            $forecastHours[] = new ForecastHour($hour, $precipitation, $uvIndex);
        }

        return $forecastHours;
    }
}
