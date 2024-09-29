<?php

namespace App\Casts;

use App\Models\Forecast\Values;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ForecastValues implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Values
    {
        $data = $value ? json_decode($value, true) : null;

        return !is_null($data) ? Values::fromArray($data) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|null
    {
        return $value instanceof Values ? json_encode($value->toArray()) : null;
    }
}
