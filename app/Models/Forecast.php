<?php
declare(strict_types=1);

namespace App\Models;

use App\Casts\ForecastValues;
use App\Models\Forecast\Values;
use Database\Factories\ForecastFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read int         $id
 * @property-read int         $city_id
 * @property-read City        $city
 * @property-read Carbon      $hour
 * @property-read Values      $values
 * @property-read Carbon      $created_at
 * @property-read Carbon|null $updated_at
 * @method ForecastFactory factory($count = null, $state = [])
 */
class Forecast extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'hour', 'values'];

    protected function casts(): array
    {
        return [
            'hour'   => 'datetime',
            'values' => ForecastValues::class,
        ];
    }
}
