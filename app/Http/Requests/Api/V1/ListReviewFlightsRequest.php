<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class ListReviewFlightsRequest extends FlightListRequest
{
    // Per-airline Dispatcher/Manager role - same rule as the web review pages.
    public function authorize(): bool
    {
        return $this->user()->canReviewFlightsFor($this->route('airline'));
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('You do not have permission to review flights for this airline.');
    }

    protected function filterRules(): array
    {
        return array_merge(parent::filterRules(), [
            'pilot_id' => 'sometimes|integer',
        ]);
    }

    protected function applyFilters(Builder $query): void
    {
        parent::applyFilters($query);

        $query->when($this->validated('pilot_id'), fn ($q, $id) => $q->where('pilot_id', $id));
    }
}
