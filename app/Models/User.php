<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * @property-read  int         $id
 * @property       string      $name
 * @property       string      $email
 * @property       Carbon|null $email_verified_at
 * @property-write string      $password
 * @property-write string|null $remember_token
 * @property       int|null    $city_id
 * @property-read  City|null   $city
 * @property       string|null $phone
 * @property       string|null $timezone
 * @property-read  Carbon      $created_at
 * @property       Carbon|null $updated_at
 * @method UserFactory factory($count = null, $state = [])
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'city_id', 'phone', 'timezone'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'channels'          => 'array',
        ];
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @param string            $driver
     * @param Notification|null $notification
     *
     * @return string|array|null
     */
    public function routeNotificationFor($driver, $notification = null): string|array|null
    {
        return match ($driver) {
            'mail'  => [$this->email => $this->name],
            'sms'   => $this->phone,
            default => null,
        };
    }
}
