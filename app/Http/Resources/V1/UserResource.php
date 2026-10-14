<?php

namespace App\Http\Resources\V1;

use App\Support\Abilities;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private bool $withAbilities = false;

    /**
     * Add the instance `abilities` and each airline's `membership` - only for
     * the token owner on GET /api/v1/user, so pilots nested in flight payloads
     * stay lean.
     */
    public function withAbilities(): static
    {
        $this->withAbilities = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Only the user themself gets to see their email address, abilities and
        // memberships - this resource is also nested as "pilot" in flight payloads.
        $withAbilities = $this->withAbilities && (bool) $request->user()?->is($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when((bool) $request->user()?->is($this->resource), $this->email),
            'abilities' => $this->when($withAbilities, fn () => Abilities::forInstance($this->resource)),
            'airlines' => $this->whenLoaded('airlines', fn () => $this->airlines->map(
                fn ($airline) => $withAbilities ? (new AirlineResource($airline))->withMembership() : new AirlineResource($airline)
            )),
        ];
    }
}
