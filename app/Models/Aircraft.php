<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Concerns\LogsModelActivity;

class Aircraft extends Model
{
   use HasFactory, LogsModelActivity;

    protected $fillable = [
        'registration',
        'manufacturer',
        'model',
        'engine_type',
        'satcom',
        'winglets',
        'selcal',
        'hex_code',
        'msn',
        'mtow',
        'mzfw',
        'mlw',
        'remarks',
        'current_loc',
        'used_by',
        'status',
        'retired_at',
        'retired_reason',
    ];

    protected $casts = [
        'satcom' => 'boolean',
        'winglets' => 'boolean',
        'retired_at' => 'datetime',
    ];

    // Lifecycle states stored in the `status` column.
    public const STATUS_ACTIVE = 'active';       // in service, flyable
    public const STATUS_INACTIVE = 'inactive';   // temporarily grounded, reversible
    public const STATUS_RETIRED = 'retired';     // permanently removed, irreversible

    protected $appends = [
        'full_type',
        'total_flights_count',
        'total_flights_hours',
        'total_distance_flown',
        'in_service_since',
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

    public function flights()
    {
        return $this->hasMany(Flight::class, 'aircraft_id');
    }

    // Screenshot gallery. Ordered so the primary (livery) shot comes first.
    public function images()
    {
        return $this->hasMany(AircraftImage::class, 'aircraft_id')
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at');
    }

    // The single shot that best shows the aircraft's livery (may be null).
    public function primaryImage()
    {
        return $this->hasOne(AircraftImage::class, 'aircraft_id')->where('is_primary', true);
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

    public function getTotalDistanceFlownAttribute() {
        $flights = Flight::where('aircraft_id', '=', $this->id)->where('status_id', '=', 2)->get();

        $totalDistance = 0;
        foreach ($flights as $flight) {
            $totalDistance += $flight->raw_distance ?? 0;
        }

        return $totalDistance;
    }

    // Returns a bool if an aircraft is owned by $airline
    public function ownedBy(Airline $airline): bool
    {
        return $this->used_by === $airline->id;
    }

    public function getInServiceSinceAttribute() {
        return $this->created_at->format('Y-m-d');
    }

    // Backwards-compatible convenience accessor: `active` is now derived from `status`
    // so existing views/resources reading $aircraft->active keep working.
    public function getActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRetired(): bool
    {
        return $this->status === self::STATUS_RETIRED;
    }

    // Only aircraft that are currently in service (flyable).
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Everything except permanently retired aircraft.
    public function scopeNotRetired($query)
    {
        return $query->where('status', '<>', self::STATUS_RETIRED);
    }

}
