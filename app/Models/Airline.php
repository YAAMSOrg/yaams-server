<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


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

    //protected $appends = [
        //'airline_members'
    //];

    //public function getAirlineMembersAttribute() {
        //return $this->belongsToMany(User::class, 'airline_memberships', 'airline_id', 'user_id');
    //}

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'airline_memberships', 'airline_id', 'user_id');
    }

}
