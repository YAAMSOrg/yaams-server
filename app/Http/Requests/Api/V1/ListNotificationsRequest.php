<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Database\Eloquent\Builder;

/**
 * The token owner's own in-app notifications.
 */
class ListNotificationsRequest extends ListRequest
{
    protected function sortable(): array
    {
        return [
            'created_at' => 'created_at',
            'read_at' => 'read_at',
        ];
    }

    protected function defaultSort(): string
    {
        return '-created_at';
    }

    protected function filterRules(): array
    {
        return [
            'unread' => 'sometimes|in:1,0,true,false',
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        $query->when($this->has('unread'), fn ($q) => $this->boolean('unread')
            ? $q->whereNull('read_at')
            : $q->whereNotNull('read_at'));
    }
}
