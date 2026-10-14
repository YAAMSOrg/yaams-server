<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class ListFlightsRequest extends FlightListRequest
{
    // Public filter values, mapped to the hardcoded flight status IDs.
    public const STATUSES = ['pending' => 1, 'accepted' => 2, 'rejected' => 3];

    public function authorize(): bool
    {
        return $this->user()->isMemberOf($this->route('airline'));
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('You are not a member of this airline.');
    }

    protected function filterRules(): array
    {
        return array_merge(parent::filterRules(), [
            'status' => 'sometimes|in:'.implode(',', array_keys(self::STATUSES)),
            // Deprecated alias of `status`, kept for existing clients.
            'status_id' => 'sometimes|integer|in:'.implode(',', self::STATUSES),
        ]);
    }

    protected function applyFilters(Builder $query): void
    {
        parent::applyFilters($query);

        $statusId = $this->has('status') ? self::STATUSES[$this->validated('status')] : $this->validated('status_id');

        $query->when($statusId, fn ($q, $id) => $q->where('status_id', $id));
    }
}
