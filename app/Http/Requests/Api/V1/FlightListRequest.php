<?php

namespace App\Http\Requests\Api\V1;

use App\Support\BlockTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sort keys and filters shared by the flight lists (my flights, review queue).
 */
abstract class FlightListRequest extends ListRequest
{
    protected function sortable(): array
    {
        return [
            // Integer column, so this is a numeric sort (9 before 10).
            'flight_number' => 'flightnumber',
            'callsign' => 'callsign',
            'departure' => 'departure_icao',
            'arrival' => 'arrival_icao',
            'block_off' => 'blockoff',
            'block_on' => 'blockon',
            'duration' => fn (Builder $query) => DB::raw(BlockTime::secondsSql(
                $query->getConnection(),
                $query->getGrammar()->wrap($query->qualifyColumn('blockoff')),
                $query->getGrammar()->wrap($query->qualifyColumn('blockon')),
            )),
            'burned_fuel' => 'burned_fuel',
            'created_at' => 'created_at',
        ];
    }

    protected function defaultSort(): string
    {
        return '-created_at';
    }

    protected function filterRules(): array
    {
        return [
            'aircraft_id' => 'sometimes|integer',
            'departure' => 'sometimes|alpha|size:4',
            'arrival' => 'sometimes|alpha|size:4',
            'from' => 'sometimes|date_format:Y-m-d',
            'to' => array_merge(['sometimes', 'date_format:Y-m-d'], $this->filled('from') ? ['after_or_equal:from'] : []),
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        // `from` / `to` are whole UTC days on block-off, `to` inclusive.
        $query->when($this->validated('aircraft_id'), fn ($q, $id) => $q->where('aircraft_id', $id))
            ->when($this->validated('departure'), fn ($q, $icao) => $q->where('departure_icao', strtoupper($icao)))
            ->when($this->validated('arrival'), fn ($q, $icao) => $q->where('arrival_icao', strtoupper($icao)))
            ->when($this->validated('from'), fn ($q, $day) => $q->where('blockoff', '>=', Carbon::parse($day, 'UTC')->startOfDay()))
            ->when($this->validated('to'), fn ($q, $day) => $q->where('blockoff', '<', Carbon::parse($day, 'UTC')->addDay()->startOfDay()));
    }
}
