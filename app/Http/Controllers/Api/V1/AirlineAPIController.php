<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAirlineRequest;
use App\Http\Resources\V1\AirlineCollection;
use App\Http\Resources\V1\AirlineResource;
use App\Models\Airline;

class AirlineAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return new AirlineCollection(Airline::all());
    }

    public function show(Airline $airline)
    {
        return new AirlineResource($airline);
    }

    /**
     * Found a new airline. The "add airlines" permission check lives in
     * StoreAirlineRequest.
     */
    public function store(StoreAirlineRequest $request)
    {
        $airline = Airline::create($request->validated());

        return (new AirlineResource($airline))
            ->response()
            ->setStatusCode(201);
    }
}
