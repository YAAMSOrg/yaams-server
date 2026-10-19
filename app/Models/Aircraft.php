<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Concerns\LogsModelActivity;
use App\Support\BlockTime;
use Illuminate\Support\Facades\DB;

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
        'service_ceiling',
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

    // Only cheap, column-derived values. The fleet totals (total_flights_count,
    // total_block_minutes, total_distance_flown, ...) run queries, so they stay
    // explicit accessors instead of running on every toArray()/toJson().
    protected $appends = [
        'full_type',
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

    // Screenshot gallery - every image regardless of moderation state (used for
    // the per-aircraft cap and lifecycle operations).
    public function images()
    {
        return $this->hasMany(AircraftImage::class, 'aircraft_id')
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at');
    }

    // Approved screenshots, primary (livery) shot first - the public gallery.
    public function approvedImages()
    {
        return $this->hasMany(AircraftImage::class, 'aircraft_id')
            ->approved()
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at');
    }

    // Pilot uploads awaiting manager review.
    public function pendingImages()
    {
        return $this->hasMany(AircraftImage::class, 'aircraft_id')
            ->pending()
            ->orderByDesc('created_at');
    }

    // The single shot that best shows the aircraft's livery (always approved,
    // may be null).
    public function primaryImage()
    {
        return $this->hasOne(AircraftImage::class, 'aircraft_id')->where('is_primary', true);
    }

    // This is a dynamic attribute, which simply adds the occurances of a flight entry with the specific $aicraft->id and all accepted flights.
    public function getTotalFlightsCountAttribute() {
        return Flight::where('aircraft_id', '=', $this->id)->where('status_id', '=', 2)->count();
    }

    // Summed block time of all accepted flights, in whole minutes. One SQL
    // aggregate instead of loading every flight.
    public function getTotalBlockMinutesAttribute(): int {
        $connection = (new Flight)->getConnection();

        $seconds = Flight::where('aircraft_id', '=', $this->id)
            ->where('status_id', '=', 2)
            ->sum(DB::raw(BlockTime::secondsSql($connection)));

        return intdiv((int) $seconds, 60);
    }

    // Decimal hours (one place), e.g. 12.5 for 12:30 of block time.
    public function getTotalFlightsHoursAttribute(): float {
        return round($this->total_block_minutes / 60, 1);
    }

    // Block time as "H:MM h", the format of a single flight's duration.
    public function getTotalBlockTimeAttribute(): string {
        $minutes = $this->total_block_minutes;

        return sprintf('%s:%02d h', number_format(intdiv($minutes, 60)), $minutes % 60);
    }

    // Great-circle distance of all accepted flights, in NM. Loads only the
    // route columns plus both airports' coordinates (3 queries in total, not
    // 2 per flight).
    public function getTotalDistanceFlownAttribute() {
        $airportCoords = 'icao_code,latitude_deg,longitude_deg';
        $flights = Flight::where('aircraft_id', '=', $this->id)
            ->where('status_id', '=', 2)
            ->select(['id', 'departure_icao', 'arrival_icao'])
            ->with(["departure_airport:{$airportCoords}", "arrival_airport:{$airportCoords}"])
            ->get();

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
