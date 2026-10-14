<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

class AirlineCollection extends ApiCollection
{
    /**
     * Every airline list is requested by the token owner, so each item
     * carries their `membership`.
     */
    public function toArray(Request $request): array
    {
        $this->collection->each->withMembership();

        return parent::toArray($request);
    }
}
