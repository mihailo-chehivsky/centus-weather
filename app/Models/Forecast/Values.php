<?php
declare(strict_types=1);

namespace App\Models\Forecast;

readonly class Values
{
    public function __construct(public float $precipitation, public float $uvIndex)
    {
    }

    public function toArray(): array
    {
        return [
            'precipitation' => $this->precipitation,
            'uv_index'      => $this->uvIndex,
        ];
    }

    public static function fromArray(array $values): self
    {
        return new self(
            precipitation: $values['precipitation'],
            uvIndex      : $values['uv_index'],
        );
    }
}
