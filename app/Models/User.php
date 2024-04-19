<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'homebase',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_online_at' => 'datetime'
    ];

    //protected $appends = [
//
    //];

    public function logged_hours(Airline $airline) {
        $flights = Flight::query()
        ->where('airline_id', '=', $airline->id)
        ->where('pilot_id', '=', $this->id)
        ->get();

        $totalFlightMinutes = 0;

        foreach ($flights as $flight) {
            $totalFlightMinutes += $flight->flight_duration_minutes;
        }
        $hours = floor($totalFlightMinutes / 60);
        $minutes = $totalFlightMinutes % 60;

        return $hours;
    }

    public function logged_flights(Airline $airline) {
        return Flight::query()
        ->where('airline_id', '=', $airline->id)
        ->where('pilot_id', '=', $this->id)
        ->get()->count();
    }

    public function airlines(): BelongsToMany
    {
        return $this->belongsToMany(Airline::class, 'airline_memberships', 'user_id', 'airline_id');
    }

    public function isMemberOf(Airline $airline) : bool
    {
        // Überprüfe, ob die Airline in der Liste der Airlines vorhanden ist, zu denen der Benutzer eine Beziehung hat
        return $this->airlines()->where('airline_id', $airline->id)->exists();
    }

}
