<?php
declare(strict_types=1);

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read int         $id
 * @property      string      $name
 * @property      float       $latitude
 * @property      float       $longitude
 * @property-read Carbon      $created_at
 * @property      Carbon|null $updated_at
 * @method CityFactory factory($count = null, $state = [])
 */
class City extends Model
{
    use HasFactory;
}
