<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'airline' => new AirlineResource($this->whenLoaded('airline')),
            'callsign' => $this->callsign,
            'flightNumber' => $this->flightnumber,
            'fullFlightNumber' => $this->full_flight_number,
            'fullIcaoCallsign' => $this->full_icao_callsign,
            'departureIcao' => $this->departure_icao,
            'arrivalIcao' => $this->arrival_icao,
            'aircraft' => new AircraftResource($this->whenLoaded('aircraft')),
            'cruiseAltitude' => $this->crzalt,
            'blockOff' => $this->blockoff,
            'blockOn' => $this->blockon,
            'duration' => $this->flight_duration,
            'burnedFuel' => $this->burned_fuel,
            'route' => $this->route,
            'onlineNetwork' => $this->online_network_id, // Could be a resource if expanded
            'pilot' => $this->pilot ? new UserResource($this->whenLoaded('pilot')) : null,
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status->name ?? 'Unknown',
            ],
            'remarks' => $this->remarks,
            'rejectionRemarks' => $this->rejection_remarks,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
