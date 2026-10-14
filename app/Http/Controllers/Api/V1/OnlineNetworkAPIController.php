<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OnlineNetworkResource;
use App\Models\OnlineNetwork;

/**
 * @group Online networks
 *
 * Reference data for filing PIREPs.
 */
class OnlineNetworkAPIController extends Controller
{
    /**
     * List online networks
     *
     * The online networks a flight can be filed on - pass the `id` as
     * `online_network_id` when filing a PIREP. IDs differ between instances,
     * so never hardcode them. Unpaginated, since the list is small and stable.
     *
     * @response 200 {
     *   "data": [
     *     {"id": 1, "name": "Offline"},
     *     {"id": 2, "name": "VATSIM"},
     *     {"id": 3, "name": "IVAO"},
     *     {"id": 4, "name": "PilotEdge"}
     *   ]
     * }
     */
    public function index()
    {
        return OnlineNetworkResource::collection(OnlineNetwork::query()->orderBy('id')->get());
    }
}
