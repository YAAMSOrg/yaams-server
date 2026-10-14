<?php

namespace App\Http\Requests\Api\V1;

use Closure;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared contract of every API v1 list endpoint: `page` / `per_page`
 * pagination, a whitelisted `sort` (`-` prefix = descending) and validated
 * filters. Invalid values are a 422, never silently ignored.
 */
abstract class ListRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 25;

    public const MAX_PER_PAGE = 100;

    /**
     * Sort keys a client may use, mapped to their database column, or to a
     * closure returning a computed sort expression for the given query, e.g.
     * a duration. The closure should qualify the columns it references.
     *
     * @return array<string, string|Closure(Builder): Expression>
     */
    abstract protected function sortable(): array;

    /**
     * Sort applied when the client sends none, e.g. `-created_at`.
     */
    abstract protected function defaultSort(): string;

    /**
     * Validation rules of the endpoint-specific filters.
     *
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [];
    }

    /**
     * Narrow the query by the validated filters.
     */
    protected function applyFilters(Builder $query): void
    {
        //
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sorts = collect(array_keys($this->sortable()))
            ->flatMap(fn (string $key) => [$key, '-'.$key])
            ->all();

        return array_merge([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:'.self::MAX_PER_PAGE,
            'sort' => ['sometimes', 'string', Rule::in($sorts)],
        ], $this->filterRules());
    }

    public function messages(): array
    {
        return [
            'sort.in' => 'The sort must be one of: '.implode(', ', array_keys($this->sortable()))
                .' (prefix with - for descending).',
        ];
    }

    /**
     * Apply filters and sort, then return the requested page. Pagination
     * links keep the query string, so `next` / `prev` stay filtered.
     */
    public function paginate(Builder|Relation $query): LengthAwarePaginator
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;

        $this->applyFilters($builder);

        $sort = $this->validated('sort', $this->defaultSort());
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $column = $this->sortable()[ltrim($sort, '-')];

        // Qualified, so a sort on a pivot-joined query is never ambiguous.
        $builder->orderBy($column instanceof Closure ? $column($builder) : $builder->qualifyColumn($column), $direction)
            // Tie-breaker so rows never shift between pages.
            ->orderBy($builder->getModel()->getQualifiedKeyName(), $direction);

        return $query
            ->paginate((int) $this->validated('per_page', self::DEFAULT_PER_PAGE))
            ->withQueryString();
    }
}
