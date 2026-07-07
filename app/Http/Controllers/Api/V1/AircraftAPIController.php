<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AircraftCollection;
use App\Http\Resources\V1\AircraftResource;
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
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        //Return every aircraft which is used by the airline.
        return new AircraftCollection(Aircraft::where('used_by', '=', $airline->id)->get());
    }

    public function addAircraftForAirline(Airline $airline, Request $request){
        $get_asking_user = request()->user('sanctum');

        if(!$get_asking_user->can('add aircraft')) {
            return response()->json(['message' => 'Forbidden: You do not have permission to add aircraft.'], 403);
        }

        // Check if the user is member of the airline.
        if(!$get_asking_user->isMemberOf($airline)){
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        $validated = $request->validate([
            'registration' => 'required|max:9|regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{3,5}$/i',
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'engine_type' => 'nullable|string|max:100',
            'satcom' => 'boolean',
            'winglets' => 'boolean',
            'selcal' => 'nullable|string|max:5|regex:/^[A-Z]{2}-?[A-Z]{2}$/i',
            'hex_code' => 'nullable|string|size:6|regex:/^[a-fA-F0-9]{6}$/i',
            'msn' => 'nullable|digits_between:1,6',
            'mtow' => 'nullable|integer|min:0|max:1000000',
            'mzfw' => 'nullable|integer|min:0|max:1000000',
            'mlw' => 'nullable|integer|min:0|max:1000000',
            'remarks' => 'nullable|string|max:1000',
            'current_loc' => 'required|max:4',
        ]);

        if (empty($validated['engine_type'])) {
            $validated['engine_type'] = 'Unknown';
        }
        if (isset($validated['registration'])) {
            $validated['registration'] = strtoupper($validated['registration']);
        }
        if (isset($validated['selcal'])) {
            $validated['selcal'] = strtoupper($validated['selcal']);
        }
        if (isset($validated['hex_code'])) {
            $validated['hex_code'] = strtoupper($validated['hex_code']);
        }

        if (!Airport::find($request->post('current_loc'))) {
            throw ValidationException::withMessages(['current_loc' => 'This airport could not be found in the database.']);
        }

        // Check if user given aircraft exists for the same airline and status = active. If not, throw exception.
        if (Aircraft::where('status', Aircraft::STATUS_ACTIVE)->where('registration', '=', $request->post('registration'))->where('used_by', '=', $airline->id)->count() >= 1) {
            throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.']);
        } else {
            $aircraft = Aircraft::create($validated + ['used_by' => $airline->id]);
            return new AircraftResource($aircraft);
        }
    }
}
