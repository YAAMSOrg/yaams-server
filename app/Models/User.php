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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsModelActivity;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Support\BlockTime;
use Illuminate\Support\Facades\DB;

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
        'suspended_at' => 'datetime',
        'legal_accepted_at' => 'datetime',
    ];

    //protected $appends = [
//
    //];

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Whole block-time minutes of the user's accepted flights for an airline,
     * summed in one aggregate query.
     */
    public function logged_minutes(Airline $airline): int {
        $query = Flight::query()
        ->where('airline_id', '=', $airline->id)
        ->where('pilot_id', '=', $this->id)
        ->where('status_id', '=', 2);

        $seconds = $query->sum(DB::raw(BlockTime::secondsSql($query->getConnection())));

        return intdiv((int) $seconds, 60);
    }

    public function logged_hours(Airline $airline) {
        return intdiv($this->logged_minutes($airline), 60);
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

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class, 'pilot_id');
    }

    public function ownedAirlines(): HasMany
    {
        return $this->hasMany(Airline::class, 'owner_user_id');
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /**
     * Case-insensitive match on name or e-mail address.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
            ->where('name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")));
    }

    /**
     * The per-airline role (Pilot | Dispatcher | Manager), null for a
     * non-member. Reads the loaded `airlines` relation when present, so
     * reporting the abilities of every membership (GET /api/v1/user) costs no
     * query per check; otherwise one query.
     */
    public function airlineRole(Airline $airline): ?string
    {
        $membership = $this->relationLoaded('airlines')
            ? $this->airlines->firstWhere('id', $airline->id)
            : $this->airlines()->where('airline_id', $airline->id)->first();

        return $membership?->pivot->role;
    }

    public function isMemberOf(Airline $airline): bool
    {
        return $this->airlineRole($airline) !== null;
    }

    public function hasAirlineRole(Airline $airline, string|array $roles): bool
    {
        return in_array($this->airlineRole($airline), (array) $roles, true);
    }

    public function isOwnerOf(Airline $airline): bool
    {
        return $airline->owner_user_id === $this->id;
    }

    public function isManagerOf(Airline $airline): bool
    {
        return $this->isOwnerOf($airline) || $this->hasAirlineRole($airline, 'Manager');
    }

    public function canReviewFlightsFor(Airline $airline): bool
    {
        return $this->isOwnerOf($airline) || $this->hasAirlineRole($airline, ['Dispatcher', 'Manager']);
    }

    public function countNewNotifications() {
        return $this->unreadNotifications()->count();
    }

}
