<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Airline;

use DateTime;

class Flight extends Model
{
    use HasFactory;

    protected $appends = [
        'full_flight_number',
        'full_icao_callsign',
        'flight_duration',
        'flight_duration_minutes',
        'flight_date'
    ];

    public function airline() {
        return $this->belongsTo(Airline::class, 'airline');
    }

    public function getFullFlightNumberAttribute() {
        return $this->Airline->prefix . $this->flightnumber;
    }

    public function getFullIcaoCallsignAttribute() {
        return $this->Airline->icao_callsign . $this->flightnumber;
    }

    public function getFlightDurationAttribute() {
        $blockofftime = DateTime::createFromFormat('Y-m-d H:i:s', $this->blockoff);
        $blockontime = DateTime::createFromFormat('Y-m-d H:i:s', $this->blockon);
        
        if ($blockofftime === false || $blockontime === false) {
            return "Error while parsing flight duration time.";
        }

        $duration = $blockofftime->diff($blockontime);

        return $duration->format('%h:%i h');
    }

    public function getFlightDurationMinutesAttribute() {
        $blockofftime = DateTime::createFromFormat('Y-m-d H:i:s', $this->blockoff);
        $blockontime = DateTime::createFromFormat('Y-m-d H:i:s', $this->blockon);
        
        if ($blockofftime === false || $blockontime === false) {
            return "Error while parsing flight duration time.";
        }
    
        $duration = $blockofftime->diff($blockontime);
    
        // Die Dauer in Minuten berechnen
        $hoursInMinutes = $duration->h * 60;
        $totalMinutes = $hoursInMinutes + $duration->i;
    
        return $totalMinutes;
    }

    public function getFlightDateAttribute() {
       // We take the block on time and take only the year, month and date.
       $blockontime = DateTime::createFromFormat('Y-m-d H:i:s', $this->blockon);
       
       return $blockontime->format('Y/m/d');
    }
}
