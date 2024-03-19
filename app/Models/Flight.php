<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Aircraft;

class Flight extends Model
{
    use HasFactory;

    protected int $airline = 1;
    protected String $callsign = '';
    protected String $flightnumber = '';
    protected String $departureICAO = '';
    protected String $arrivalICAO = '';
    protected Aircraft $aircraft = ;
    protected ?String $remarks = null;
    protected int $usedByAirline = 1;

    protected $appends = [
        'full_flight_number',
        'full_icao_callsign'
    ];

    public function getFullFlightNumber() {
        return $this->airline.prefix . $this->flightnumber;
    }

    public function getFullICAOCallsign() {
        return $this->airline.prefix . $this->callsign;
    }
}
