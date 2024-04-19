<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AircraftCollection;
use App\Models\Aircraft;
use App\Models\Airline;
class AircraftAPIController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function listAircraftForAirline(Airline $airline){
        $get_asking_user = request()->user('sanctum');

        // Check if the user is member of the airline.
        if(!$get_asking_user->isMemberOf($airline)){
            return response()->json(['error' => 'You are not a member of this airline.'], 401);
        }

        //Return every aircraft which is used by the airline.
        return new AircraftCollection(Aircraft::where('used_by', '=', $airline->id)->get());
    }
}
