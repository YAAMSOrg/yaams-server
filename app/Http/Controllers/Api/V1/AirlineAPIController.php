<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Http\Resources\V1\AirlineResource;
use App\Http\Resources\V1\AirlineCollection;

class AirlineAPIController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(){
        return new AirlineCollection(Airline::all());
    }

    public function show(Airline $airline){
        return new AirlineResource($airline);
    }

    public function store(Request $request){
        $get_asking_user = request()->user('sanctum');

        if(!$get_asking_user->can('add airlines')){
            return response()->json(['message' => 'Forbidden: Missing "add airlines" permission.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|max:50|unique:airlines', // Example Airline
            'prefix' => 'required|min:2|max:2|unique:airlines|uppercase', // EV
            'icao_callsign' => 'required|regex:/^[a-zA-Z]+$/u|min:3|max:3|unique:airlines|uppercase', // EVA
            'atc_callsign' => 'required|regex:/^[a-zA-Z]+$/u|max:25|unique:airlines' // EXAMPLE
        ]);

        $airline = Airline::create($validated);

        return (new AirlineResource($airline))
            ->response()
            ->setStatusCode(201);
    }

}
