<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use App\Support\Abilities;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirlineResource extends JsonResource
{
    private bool $withMembership = false;

    /**
     * Add the token owner's `membership` (null for a non-member). Opt-in, so
     * airlines nested in other payloads (e.g. flights) stay lean. Load the
     * user's `airlines` relation first to keep this query-free per airline.
     */
    public function withMembership(): static
    {
        $this->withMembership = true;

        return $this;
    }

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
            'unitIsLbs' => $this->unit_is_lbs,
            'requirePirepReview' => $this->require_pirep_review,
            'locationContinuity' => $this->location_continuity,
            'isSuspended' => $this->isSuspended(),
            'membership' => $this->when($this->withMembership, fn () => $this->membership($request->user())),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }

    /**
     * @return array{role: string, isOwner: bool, abilities: array<string, bool>}|null
     */
    private function membership(User $user): ?array
    {
        $role = $user->airlineRole($this->resource);

        if ($role === null) {
            return null;
        }

        return [
            // Stable machine key: pilot | dispatcher | manager.
            'role' => strtolower($role),
            'isOwner' => $user->isOwnerOf($this->resource),
            'abilities' => Abilities::forAirline($user, $this->resource),
        ];
    }
}
