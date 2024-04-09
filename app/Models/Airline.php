<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
        'icao_callsign',
        'atc_callsign',
        'unit_is_lbs'
    ];

    /***
     * Returns all User Models, which are members of the airline.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'airline_memberships', 'airline_id', 'user_id');
    }

    /***
     * Helper function, to check if a given user is member of a specific airline. 
     * For example, if you want to check if the logged in User is member of the airline instance, you can do:
     * $airline->isMember(auth()->user()); and it will return true or false.
     */
    public function isMember(User $user): bool {
        $usercheck = DB::table('airline_memberships')
                ->where('airline_id', '=', $this->id)
                ->where('user_id', '=', $user->id)
                ->count();

        if($usercheck == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function flights() {
        return $this->hasMany(Flight::class, 'airline_id');
    }

}
