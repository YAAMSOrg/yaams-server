<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            // Only the user themself gets to see their email address - this
            // resource is also nested as "pilot" in flight payloads.
            'email' => $this->when((bool) $request->user()?->is($this->resource), $this->email),
        ];
    }
}
