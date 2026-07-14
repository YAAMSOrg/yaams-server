<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Concerns\LogsModelActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsModelActivity, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'email_notifications',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_online_at' => 'datetime',
        'email_notifications' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    //protected $appends = [
//
    //];

    public function logged_hours(Airline $airline) {
        $flights = Flight::query()
        ->where('airline_id', '=', $airline->id)
        ->where('pilot_id', '=', $this->id)
        ->where('status_id', '=', 2)
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
        ->where('status_id', '=', 2)
        ->count();
    }

    public function airlines(): BelongsToMany
    {
        return $this->belongsToMany(Airline::class, 'airline_memberships', 'user_id', 'airline_id')
                    ->withPivot('role');
    }

    public function isMemberOf(Airline $airline): bool
    {
        return $this->airlines()->where('airline_id', $airline->id)->exists();
    }

    public function hasAirlineRole(Airline $airline, string|array $roles): bool
    {
        return $this->airlines()
            ->where('airline_id', $airline->id)
            ->wherePivotIn('role', (array) $roles)
            ->exists();
    }

    public function isManagerOf(Airline $airline): bool
    {
        return $this->hasAirlineRole($airline, 'Manager');
    }

    public function canReviewFlightsFor(Airline $airline): bool
    {
        return $this->hasAirlineRole($airline, ['Dispatcher', 'Manager']);
    }

    public function countNewNotifications() {
        return $this->unreadNotifications()->count();
    }

}
