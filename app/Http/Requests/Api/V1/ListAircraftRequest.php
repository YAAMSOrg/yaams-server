<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Aircraft;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class ListAircraftRequest extends ListRequest
{
    public function authorize(): bool
    {
        return $this->user()->isMemberOf($this->route('airline'));
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('You are not a member of this airline.');
    }

    protected function sortable(): array
    {
        return [
            'registration' => 'registration',
            'model' => 'model',
            'created_at' => 'created_at',
        ];
    }

    protected function defaultSort(): string
    {
        return 'registration';
    }

    protected function filterRules(): array
    {
        return [
            'status' => ['sometimes', Rule::in([Aircraft::STATUS_ACTIVE, Aircraft::STATUS_INACTIVE, Aircraft::STATUS_RETIRED])],
            'location' => 'sometimes|alpha|size:4',
            'search' => 'sometimes|string|max:100',
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        $search = trim((string) $this->validated('search'));

        $query->when($this->validated('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($this->validated('location'), fn ($q, $icao) => $q->where('current_loc', strtoupper($icao)))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('registration', 'LIKE', "%{$search}%")
                ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                ->orWhere('model', 'LIKE', "%{$search}%")));
    }
}
