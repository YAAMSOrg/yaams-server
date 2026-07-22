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
            'service_ceiling' => $this->service_ceiling,
            'remarks' => $this->remarks,

            // We don't have to return this, since this can only be accessed through a route, which already specifies the airline.
            //'usedBy' => $this->used_by,

            'status' => $this->status,
            'active' => $this->active,
            'retiredAt' => $this->retired_at,
            'retiredReason' => $this->retired_reason,
            'inServiceSince' => $this->in_service_since,
            'firstFlight' => $this->first_flight,

            // Absolute URL to the primary (livery) screenshot, or null. The route
            // itself enforces that only authorized viewers can fetch the bytes.
            'primaryImageUrl' => $this->primaryImage
                ? route('aircraft.images.show', [$this->id, $this->primaryImage->id])
                : null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
