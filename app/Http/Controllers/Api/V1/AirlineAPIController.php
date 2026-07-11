<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAirlineRequest;
use App\Http\Resources\V1\AirlineCollection;
use App\Http\Resources\V1\AirlineResource;
use App\Models\Airline;
use Illuminate\Http\Request;

/**
 * @group Airlines
 *
 * Virtual airlines. Aircraft and flights are nested resources of an airline.
 */
class AirlineAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List airlines
     *
     * @queryParam members_only boolean Only return airlines the token owner belongs to. Defaults to all airlines. Example: true
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Example Virtual Airlines",
     *       "prefix": "EV",
     *       "icaoCallsign": "EVA",
     *       "atcCallsign": "EXAMPLE",
     *       "unitIsLbs": false,
     *       "requirePirepReview": true,
     *       "locationContinuity": false,
     *       "createdAt": "2026-01-01T00:00:00.000000Z",
     *       "updatedAt": "2026-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        if ($request->boolean('members_only')) {
            return new AirlineCollection($request->user()->airlines);
        }

        return new AirlineCollection(Airline::all());
    }

    /**
     * Show an airline
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Example Virtual Airlines",
     *     "prefix": "EV",
     *     "icaoCallsign": "EVA",
     *     "atcCallsign": "EXAMPLE",
     *     "unitIsLbs": false,
     *     "requirePirepReview": true,
     *     "locationContinuity": false,
     *     "createdAt": "2026-01-01T00:00:00.000000Z",
     *     "updatedAt": "2026-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Airline] 999"}
     */
    public function show(Airline $airline)
    {
        return new AirlineResource($airline);
    }

    /**
     * Found a new airline
     *
     * Requires the "add airlines" permission (enforced by StoreAirlineRequest).
     *
     * @bodyParam name string required Display name, max 50 chars, unique. Example: Example Virtual Airlines
     * @bodyParam prefix string required Two-letter IATA-style prefix, unique, uppercased. Example: EV
     * @bodyParam icao_callsign string required Three-letter ICAO callsign, unique, uppercased. Example: EVA
     * @bodyParam atc_callsign string required Spoken ATC callsign, max 25 chars, unique. Example: EXAMPLE
     *
     * @response 201 {
     *   "data": {
     *     "id": 2,
     *     "name": "Example Virtual Airlines",
     *     "prefix": "EV",
     *     "icaoCallsign": "EVA",
     *     "atcCallsign": "EXAMPLE",
     *     "unitIsLbs": false,
     *     "requirePirepReview": true,
     *     "locationContinuity": false,
     *     "createdAt": "2026-01-01T00:00:00.000000Z",
     *     "updatedAt": "2026-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The prefix has already been taken.", "errors": {"prefix": ["The prefix has already been taken."]}}
     */
    public function store(StoreAirlineRequest $request)
    {
        $airline = Airline::create($request->validated());

        return (new AirlineResource($airline))
            ->response()
            ->setStatusCode(201);
    }
}
