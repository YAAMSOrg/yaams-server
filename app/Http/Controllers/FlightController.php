<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airline;

class FlightController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function displayAllFlights() {
        $flights = Flight::query()
        ->orderBy('created_at', 'DESC')
        ->get();

        return view('flights.list', ['flights' => $flights]);
    }

    public function displayFlightsForUser() {
        $current_auth_user_id = auth()->id();

        $flights = Flight::query()
        ->where('pilot', $current_auth_user_id)
        ->orderBy('created_at', 'DESC')
        ->get();

        return view('flights.list', ['flights' => $flights]);
    }

    public function addFlight(Request $request){
        if($request->getMethod() == "POST"){
            $request->validate([
                'pilot_id' => 'required|max:255',
                'airline_id' => 'required',
                'flight_number' => 'required|max:4',
                'departure' => 'required|max:4',
                'arrival' => 'required|max:4',
                'aircraft' => 'required',
                'callsign' => 'required|max:7',
                'cruise_alt' => 'required|max:5',
                'block_off' => 'required',
                'block_on' => 'required',
                'burned_fuel' => 'required',
                'route' => 'required',
                'online_network' => 'required',
                'remarks' => 'regex:/^([a-zA-Z]+)(\s[a-zA-Z]+)*$/'
            ]);

            Flight::create($validated);
        }

        //TODO: Get all airlines, where the pilot is member of
        $prefill_select_airline = Airline::query()->get();

        //dump($prefill_select_airline);

        return view('flights.add', [ 'prefill_airline' => $prefill_select_airline ]);
    }
}
