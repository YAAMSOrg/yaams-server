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

    public function getTotalFlightsCountAttribute() {
        return Flight::where('aircraft', '=', $this->id)->count();
    }

    public function getTotalFlightsHoursAttribute() {
        $flights = Flight::where('aircraft', '=', $this->id)->get();

        $totalFlightMinutes = 0;

        foreach ($flights as $flight) {
            $totalFlightMinutes += $flight->flight_duration_minutes;
        }
        $hours = floor($totalFlightMinutes / 60);
        $minutes = $totalFlightMinutes % 60;

        return $hours;
    }

}