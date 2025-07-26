<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Aircraft extends Model
{
   use HasFactory;

   protected $fillable = [
        'registration',
        'manufacturer',
        'model',
        'remarks',
        'current_loc',
        'used_by'
    ];

    protected $appends = [
        'full_type',
        'total_flights_count',
        'total_flights_hours'
    ];

    public function getFullTypeAttribute()
    {
        return $this->manufacturer . ' ' . $this->model;
    }

    public function getRecentLocation() {
        // TODO
    }

    public function location()
    {
        return $this->belongsTo(Airport::class, 'current_loc');
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class, 'used_by');
    }

    // This is a dynamic attribute, which simply adds the occurances of a flight entry with the specific $aicraft->id and all accepted flights.
    public function getTotalFlightsCountAttribute() {
        return Flight::where('aircraft_id', '=', $this->id)->where('status_id', '=', 2)->count();
    }

    public function getTotalFlightsHoursAttribute() {
        // Get all flights with the aircraft_id and where the flight is marked as accepted.
        $flights = Flight::where('aircraft_id', '=', $this->id)->where('status_id', '=', 2)->get();

        // Initialize a var
        $totalFlightMinutes = 0;

        // Loop through each found flight and add it
        foreach ($flights as $flight) {
            $totalFlightMinutes += $flight->flight_duration_minutes;
        }

        // Convert to hours
        $hours = floor($totalFlightMinutes / 60);
        $minutes = $totalFlightMinutes % 60;

        return $hours;
    }

    /* Why do we need this?
    public function activeAndOwnedBy(Airline $airline)
    {
        return $this->used_by === $airline->id && $this->active == 1;
    }
    */

    // Returns a bool if an aircraft is owned by $airline
    public function ownedBy(Airline $airline): bool
    {
        return $this->used_by === $airline->id;
    }

}
