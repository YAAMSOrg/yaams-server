<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAircraftRequest;
use App\Http\Resources\V1\AircraftCollection;
use App\Http\Resources\V1\AircraftResource;
use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Http\Request;

/**
 * @group Aircraft
 *
 * An airline's fleet. Aircraft are scoped to their airline - an aircraft that
 * does not belong to {airline} resolves as a 404.
 */
class AircraftAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List fleet
     *
     * List the airline's aircraft.
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 7,
     *       "registration": "D-EXAM",
     *       "manufacturer": "Airbus",
     *       "model": "A320-200",
     *       "currentLoc": "EDDF",
     *       "engineType": "CFM56",
     *       "satcom": false,
     *       "winglets": true,
     *       "selcal": "AB-CD",
     *       "hexCode": "3C6444",
     *       "msn": "1234",
     *       "mtow": 78000,
     *       "mzfw": 62500,
     *       "mlw": 66000,
     *       "remarks": null,
     *       "status": "active",
     *       "active": true,
     *       "retiredAt": null,
     *       "retiredReason": null,
     *       "inServiceSince": "2020-01-01",
     *       "firstFlight": "2019-11-15",
     *       "createdAt": "2026-01-01T00:00:00.000000Z",
     *       "updatedAt": "2026-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     * @response 403 {"message": "You are not a member of this airline."}
     */
    public function index(Request $request, Airline $airline)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        return new AircraftCollection($airline->aircraft()->get());
    }

    /**
     * Show an aircraft
     *
     * The scoped route binding 404s any aircraft that does not belong to {airline}.
     *
     * @urlParam airline int required The airline ID. Example: 1
     * @urlParam aircraft int required The aircraft ID (must belong to the airline). Example: 7
     *
     * @response 200 {
     *   "data": {
     *     "id": 7,
     *     "registration": "D-EXAM",
     *     "manufacturer": "Airbus",
     *     "model": "A320-200",
     *     "currentLoc": "EDDF",
     *     "engineType": "CFM56",
     *     "satcom": false,
     *     "winglets": true,
     *     "selcal": "AB-CD",
     *     "hexCode": "3C6444",
     *     "msn": "1234",
     *     "mtow": 78000,
     *     "mzfw": 62500,
     *     "mlw": 66000,
     *     "remarks": null,
     *     "status": "active",
     *     "active": true,
     *     "retiredAt": null,
     *     "retiredReason": null,
     *     "inServiceSince": "2020-01-01",
     *     "firstFlight": "2019-11-15",
     *     "createdAt": "2026-01-01T00:00:00.000000Z",
     *     "updatedAt": "2026-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 403 {"message": "You are not a member of this airline."}
     * @response 404 {"message": "No query results for model [App\\Models\\Aircraft] 999"}
     */
    public function show(Request $request, Airline $airline, Aircraft $aircraft)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        return new AircraftResource($aircraft);
    }

    /**
     * Add an aircraft
     *
     * Add an aircraft to the airline's fleet. Requires the "add aircraft"
     * permission and membership of the airline. Permission, membership,
     * normalization and the duplicate-registration rule are enforced by
     * StoreAircraftRequest.
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @bodyParam registration string required Tail number, e.g. D-EXAM (max 9 chars, uppercased). Example: D-EXAM
     * @bodyParam manufacturer string required Airframe manufacturer, max 100 chars. Example: Airbus
     * @bodyParam model string required Aircraft model, max 100 chars. Example: A320-200
     * @bodyParam current_loc string required ICAO code of the aircraft's current location (must exist in airports). Example: EDDF
     * @bodyParam engine_type string Engine type, max 100 chars. Example: CFM56
     * @bodyParam satcom boolean Whether the aircraft has SATCOM. Example: false
     * @bodyParam winglets boolean Whether the aircraft has winglets. Example: true
     * @bodyParam selcal string SELCAL code, format XX-XX. Example: AB-CD
     * @bodyParam hex_code string Mode-S hex code, 6 hex chars. Example: 3C6444
     * @bodyParam msn string Manufacturer serial number, 1-6 digits. Example: 1234
     * @bodyParam mtow integer Max take-off weight (kg), 0-1000000. Example: 78000
     * @bodyParam mzfw integer Max zero-fuel weight (kg), 0-1000000. Example: 62500
     * @bodyParam mlw integer Max landing weight (kg), 0-1000000. Example: 66000
     * @bodyParam remarks string Free-text remarks, max 1000 chars. Example: Delivered new.
     *
     * @response 201 {
     *   "data": {
     *     "id": 7,
     *     "registration": "D-EXAM",
     *     "manufacturer": "Airbus",
     *     "model": "A320-200",
     *     "currentLoc": "EDDF",
     *     "engineType": "CFM56",
     *     "satcom": false,
     *     "winglets": true,
     *     "selcal": "AB-CD",
     *     "hexCode": "3C6444",
     *     "msn": "1234",
     *     "mtow": 78000,
     *     "mzfw": 62500,
     *     "mlw": 66000,
     *     "remarks": null,
     *     "status": "active",
     *     "active": true,
     *     "retiredAt": null,
     *     "retiredReason": null,
     *     "inServiceSince": null,
     *     "firstFlight": null,
     *     "createdAt": "2026-07-11T12:00:00.000000Z",
     *     "updatedAt": "2026-07-11T12:00:00.000000Z"
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The registration field is required.", "errors": {"registration": ["The registration field is required."]}}
     */
    public function store(StoreAircraftRequest $request, Airline $airline)
    {
        $validated = $request->validated();

        if (empty($validated['engine_type'])) {
            $validated['engine_type'] = 'Unknown';
        }

        $aircraft = Aircraft::create($validated + ['used_by' => $airline->id]);

        return (new AircraftResource($aircraft))
            ->response()
            ->setStatusCode(201);
    }
}
