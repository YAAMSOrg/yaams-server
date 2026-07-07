<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAircraftRequest;
use App\Http\Resources\V1\AircraftCollection;
use App\Http\Resources\V1\AircraftResource;
use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Http\Request;

class AircraftAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List the airline's fleet.
     */
    public function index(Request $request, Airline $airline)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        return new AircraftCollection($airline->aircraft()->get());
    }

    /**
     * Show a single aircraft. The scoped route binding 404s any aircraft
     * that does not belong to {airline}.
     */
    public function show(Request $request, Airline $airline, Aircraft $aircraft)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        return new AircraftResource($aircraft);
    }

    /**
     * Add an aircraft to the airline's fleet. Permission, membership,
     * normalization and the duplicate-registration rule are enforced by
     * StoreAircraftRequest.
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
