<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListAirlinesRequest;
use App\Http\Requests\Api\V1\StoreAirlineRequest;
use App\Http\Resources\V1\AirlineCollection;
use App\Http\Resources\V1\AirlineResource;
use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Each airline carries the token owner's `membership` (`null` for a
     * non-member) - see "Current user" for the role and ability keys.
     *
     * @queryParam members_only boolean Only return airlines the token owner belongs to. Defaults to all airlines. Example: true
     * @queryParam search string Match on name, ICAO callsign, prefix or ATC callsign. Example: EVA
     * @queryParam suspended boolean `true` only suspended airlines, `false` only active ones. Defaults to both. Example: false
     * @queryParam sort string `name`, `icao_callsign` or `created_at`, prefix with `-` for descending. Defaults to `name`. Example: name
     * @queryParam page integer Page number, starting at 1. Example: 1
     * @queryParam per_page integer Items per page, 1 - 100. Defaults to 25. Example: 25
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
     *       "isSuspended": false,
     *       "membership": {
     *         "role": "pilot",
     *         "isOwner": false,
     *         "abilities": {
     *           "fileFlights": true,
     *           "reviewFlights": false,
     *           "addAircraft": false,
     *           "editAircraft": false,
     *           "manageMembers": false,
     *           "manageInviteCodes": false,
     *           "manageNotams": false,
     *           "editSettings": false
     *         }
     *       },
     *       "createdAt": "2026-01-01T00:00:00.000000Z",
     *       "updatedAt": "2026-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "links": {"first": "https://yaams.example/api/v1/airlines?page=1", "last": "https://yaams.example/api/v1/airlines?page=1", "prev": null, "next": null},
     *   "meta": {"current_page": 1, "from": 1, "last_page": 1, "path": "https://yaams.example/api/v1/airlines", "per_page": 25, "to": 3, "total": 3}
     * }
     */
    public function index(ListAirlinesRequest $request)
    {
        // Each item carries the token owner's membership - load it once.
        $request->user()->loadMissing('airlines');

        $query = $request->boolean('members_only') ? $request->user()->airlines() : Airline::query();

        return new AirlineCollection($request->paginate($query));
    }

    /**
     * Show an airline
     *
     * Includes the token owner's `membership` (`null` for a non-member) - see
     * "Current user" for the role and ability keys.
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
     *     "isSuspended": false,
     *     "membership": {
     *       "role": "pilot",
     *       "isOwner": false,
     *       "abilities": {
     *         "fileFlights": true,
     *         "reviewFlights": false,
     *         "addAircraft": false,
     *         "editAircraft": false,
     *         "manageMembers": false,
     *         "manageInviteCodes": false,
     *         "manageNotams": false,
     *         "editSettings": false
     *       }
     *     },
     *     "createdAt": "2026-01-01T00:00:00.000000Z",
     *     "updatedAt": "2026-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Airline] 999"}
     */
    public function show(Request $request, Airline $airline)
    {
        $request->user()->loadMissing('airlines');

        return (new AirlineResource($airline))->withMembership();
    }

    /**
     * Found a new airline
     *
     * The token owner becomes the airline's owner and Manager; PIREP review
     * starts switched on, like founding on the web. Allowed for
     * Super-Admins, and for everyone when the instance allows user airline
     * creation - see `abilities.foundAirline` on `GET /api/v1/user`.
     *
     * @bodyParam name string required Display name, max 50 chars, unique. Example: Example Virtual Airlines
     * @bodyParam prefix string required Two-letter IATA-style prefix, unique, uppercased. Example: EV
     * @bodyParam icao_callsign string required Three-letter ICAO callsign, unique, uppercased. Example: EVA
     * @bodyParam atc_callsign string required Spoken ATC callsign, max 25 chars, unique. Example: EXAMPLE
     * @bodyParam hub string required ICAO code of the home airport (case-insensitive). Example: EDDF
     * @bodyParam country string required ISO 3166-1 alpha-2 country code (case-insensitive). Example: DE
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
     *     "isSuspended": false,
     *     "membership": {
     *       "role": "manager",
     *       "isOwner": true,
     *       "abilities": {
     *         "fileFlights": true,
     *         "reviewFlights": true,
     *         "addAircraft": true,
     *         "editAircraft": true,
     *         "manageMembers": true,
     *         "manageInviteCodes": true,
     *         "manageNotams": true,
     *         "editSettings": true
     *       }
     *     },
     *     "createdAt": "2026-01-01T00:00:00.000000Z",
     *     "updatedAt": "2026-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The prefix has already been taken.", "errors": {"prefix": ["The prefix has already been taken."]}}
     */
    public function store(StoreAirlineRequest $request)
    {
        // Like founding on the web: the founder becomes owner and Manager.
        $airline = DB::transaction(function () use ($request) {
            $airline = Airline::create($request->validated() + [
                'owner_user_id' => $request->user()->id,
                'founded_at' => now()->toDateString(),
                'require_pirep_review' => true,
            ]);
            $airline->users()->attach($request->user()->id, ['role' => 'Manager']);

            return $airline;
        });

        $request->user()->load('airlines');

        return (new AirlineResource($airline))->withMembership()
            ->response()
            ->setStatusCode(201);
    }
}
