<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AircraftCollection;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function addAircraftForAirline(Airline $airline, Request $request){
        $get_asking_user = request()->user('sanctum');

        if($get_asking_user->can('add aircraft')) { // Check user permission
            // Check if the user is member of the airline.
            if(!$get_asking_user->isMemberOf($airline)){
                return response()->json(['error' => 'You are not a member of this airline.'], 401);
            }

            $validated = $request->validate([
                'registration' => 'required|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'current_loc' => 'required|max:4',
                'remarks' => 'nullable',
            ]);

            if (!Airport::find($request->post('current_loc'))) {
                throw ValidationException::withMessages(['current_loc' => 'This airport could not be found in the database.']);
            }

            // Check if user given aircraft exists for the same airline and status = active. If not, throw exception.
            if (Aircraft::where('active', 1)->where('registration', '=', $request->post('registration'))->where('used_by', '=', $airline->id)->count() >= 1) {
                throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.']);
            } else {
                Aircraft::create($validated + ['used_by' => $airline->id]);
                return response()->json(['message' => 'New aircraft ' . $request->registration . ' stored succesfully.']);
            }
        } else {
            return response()->json(['error' => 'You are not a member of this airline.'], 401);
        }
    }
}
