<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirlineResource extends JsonResource
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
            'name' => $this->name,
            'prefix' => $this->prefix,
            'icaoCallsign' => $this->icao_callsign,
            'atcCallsign' => $this->atc_callsign,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
