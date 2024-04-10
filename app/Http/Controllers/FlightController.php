<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\OnlineNetwork;
use App\Models\Aircraft;

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
        $currentActiveAirline = Session()->get('activeairline');
        
        $flights = Flight::query()
        ->where('pilot_id', $current_auth_user_id)
        ->where('airline_id', $currentActiveAirline->id)
        ->orderBy('created_at', 'DESC')
        ->get();

        return view('flights.list', ['flights' => $flights]);
    }

    public function addFlight(Request $request){
        $currentActiveAirline = Session()->get('activeairline');

        if($request->getMethod() == "POST"){
            $validated = $request->validate([
                'flightnumber' => 'numeric|digits_between:1,4|required',
                'departure_icao' => 'alpha|max:4|required',
                'arrival_icao' => 'alpha|max:4|required',
                'aircraft_id' => 'numeric|required',
                'callsign' => 'alpha_num|max:7|required',
                'crzalt' => 'numeric|max:50000|digits_between:1,5|required',
                'blockoff' => 'required',
                'blockon' => 'required',
                'burned_fuel' => 'numeric|required',
                'route' => 'required',
                'online_network_id' => 'required',
                'remarks' => ''
            ]);
            
            // TODO: Maybe a few checks are needed here?
            Flight::create($validated + ['airline_id' => $currentActiveAirline->id, 'pilot_id' => auth()->user()->id]);

            return redirect()->route('flightlist');
        }

        $prefill_select_online_network = OnlineNetwork::query()->get();
        $prefill_select_aircraft = $currentActiveAirline->aircraft;

        return view('flights.add', [ 'prefill_online_network' => $prefill_select_online_network,
                                     'prefill_aircraft' => $prefill_select_aircraft ]);
    }

    public function view(Flight $flight) {
        // TODO: Check if user is in the correct airline.
        
        return view('flights.detail', ['flight' => $flight ]);
    }
}
