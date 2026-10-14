<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Base of every API v1 list. Paginated lists get Laravel's standard `links`
 * and `meta` blocks, minus `meta.links` (an HTML pager array that is
 * redundant with `links` and `meta.last_page`).
 */
abstract class ApiCollection extends ResourceCollection
{
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        unset($default['meta']['links']);

        return $default;
    }
}
