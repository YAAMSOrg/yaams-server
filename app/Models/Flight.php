<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Airline;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use DateTime;
use App\Models\Concerns\LogsModelActivity;

class Flight extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'airline_id',
        'callsign',
        'flightnumber',
        'departure_icao',
        'arrival_icao',
        'aircraft_id',
        'crzalt',
        'blockoff',
        'blockon',
        'burned_fuel',
        'route',
        'online_network_id',
        'pilot_id',
        'status',
        'remarks'
    ];

    protected $appends = [
        'full_flight_number',
        'full_icao_callsign',
        'flight_duration',
        'flight_duration_minutes',
        'flight_date',
        'raw_distance'
    ];

    public function airline() {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function aircraft() {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function status() {
        return $this->belongsTo(FlightStatus::class, 'status_id');
    }

    public function pilot(){
        return $this->belongsTo(User::class, 'pilot_id');
    }

    public function departure_airport() {
        return $this->belongsTo(Airport::class, 'departure_icao');
    }

    public function arrival_airport() {
        return $this->belongsTo(Airport::class, 'arrival_icao');
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

        return $duration->format('%H:%I h');
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

    public function getRawDistanceAttribute() 
    {
        // Flughäfen über die Beziehungen laden
        $departure = $this->departure_airport;
        $arrival = $this->arrival_airport;

        // Sicherheitsprüfung: Fehlen Daten, geben wir null zurück
        if (!$departure || !$arrival || !$departure->latitude_deg || !$departure->longitude_deg || !$arrival->latitude_deg || !$arrival->longitude_deg) {
            return null;
        }

        // Koordinaten von Grad in Bogenmaß (Radiant) umwandeln
        $lat1 = deg2rad($departure->latitude_deg);
        $lon1 = deg2rad($departure->longitude_deg);
        $lat2 = deg2rad($arrival->latitude_deg);
        $lon2 = deg2rad($arrival->longitude_deg);

        // Erdradius in nautischen Meilen (nm)
        $earthRadiusNm = 3440.065;

        // Differenzen berechnen
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        // Haversine Formel
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));

        // Distanz berechnen und auf ganze Meilen runden
        return round($angle * $earthRadiusNm);
    }

}
