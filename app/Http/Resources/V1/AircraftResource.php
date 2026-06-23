<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AircraftResource extends JsonResource
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
            'registration' => $this->registration,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'currentLoc' => $this->current_loc,
            'engineType' => $this->engine_type,
            'satcom' => $this->satcom,
            'winglets' => $this->winglets,
            'selcal' => $this->selcal,
            'hexCode' => $this->hex_code,
            'msn' => $this->msn,
            'mtow' => $this->mtow,
            'mzfw' => $this->mzfw,
            'mlw' => $this->mlw,
            'remarks' => $this->remarks,

            // We don't have to return this, since this can only be accessed through a route, which already specifies the airline.
            //'usedBy' => $this->used_by,

            'active' => $this->active,
            'inServiceSince' => $this->in_service_since,
            'firstFlight' => $this->first_flight,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
