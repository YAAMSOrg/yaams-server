<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Filters shared by the flight lists (my flights, review queue).
 */
abstract class FlightListRequest extends ListRequest
{
    protected function sortable(): array
    {
        return [
            'block_off' => 'blockoff',
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
