<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Database\Eloquent\Builder;

class ListAirlinesRequest extends ListRequest
{
    protected function sortable(): array
    {
        return [
            'name' => 'name',
            'icao_callsign' => 'icao_callsign',
            'created_at' => 'created_at',
        ];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    protected function filterRules(): array
    {
        // Laravel's `boolean` rule refuses the `true` / `false` a client
        // serialises into a query string, so the flags list them explicitly.
        return [
            'members_only' => 'sometimes|in:1,0,true,false',
            'search' => 'sometimes|string|max:100',
            'suspended' => 'sometimes|in:1,0,true,false',
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        $query->search($this->validated('search'))
            ->when($this->has('suspended'), fn ($q) => $this->boolean('suspended') ? $q->suspended() : $q->active());
    }
}
