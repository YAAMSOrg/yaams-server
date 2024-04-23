<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\OnlineNetwork;
use App\Models\Aircraft;
use App\Models\Airport;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

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

    public function displayFlightsForUser(Request $request) {
        $current_auth_user_id = auth()->id();
        $currentActiveAirline = Session()->get('activeairline');

        // This is pagination voodo.
        $limit = max(env('FLIGHT_PAGE_LIMIT'), 1);
        $maxEntries = Flight::query()
            ->where('pilot_id', $current_auth_user_id)
            ->where('airline_id', $currentActiveAirline->id)
            ->count();
        $maxPages = (int)ceil($maxEntries/$limit);
        $page = (int)$request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page -1) * $limit;

        $flights = Flight::query()
        ->where('pilot_id', $current_auth_user_id)
        ->where('airline_id', $currentActiveAirline->id)
        ->orderBy('created_at', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get();

        foreach ($flights as $flight) {
            dump($flight->status);
        }

        return view('flights.list', ['flights' => $flights, 'maxPages' => $maxPages, 'currentPage' => $page]);
    }

    public function addFlight(Request $request){
        $currentActiveAirline = Session()->get('activeairline');

        //Reload airline since we sometimes saw
        $tempAirlineID = $currentActiveAirline->id;
        $request->session()->forget('activeairline');
        $tempAirline = Airline::find($tempAirlineID);
        $request->session()->put('activeairline', $tempAirline);

        if($request->getMethod() == "POST"){
            $validated = $request->validate([
                'flightnumber' => 'numeric|digits_between:1,4|required',
                'departure_icao' => 'alpha|max:4|required',
                'arrival_icao' => 'alpha|max:4|required',
                'aircraft_id' => 'numeric|required',
                'callsign' => ['regex:/^(?:\d{1,4}|(?:\d{1}[A-Z]{2})|(?:\d{2}[A-Z]{2})|(?:\d{3}[A-Z]{1})|(?:\d{2}[A-Z]{1}))$/', 'max:7', 'required'],
                'crzalt' => 'numeric|max:50000|digits_between:1,5|required',
                'blockoff' => 'required',
                'blockon' => 'required',
                'burned_fuel' => 'numeric|required',
                'route' => 'required',
                'online_network_id' => 'required',
                'remarks' => 'nullable|regex:^[\pL\s\d]+'
            ]);

            // Check if user given airport exists, if not throw an exception. We need to do this on the two fields, to display the error.
            if (!Airport::find($request->post('departure_icao'))) {
                throw ValidationException::withMessages(['departure_icao' => 'This airport could not be found in the database.']);
            }
            if (!Airport::find($request->post('arrival_icao'))) {
                throw ValidationException::withMessages(['arrival_icao' => 'This airport could not be found in the database.']);
            }

            // Check if the aircraft is indeed part of the active airline
            if (Aircraft::query()->where('id', '=', $request->post('aircraft_id'))->where('used_by', '=', $currentActiveAirline->id)->count() == 0) {
                throw ValidationException::withMessages(['aircraft_id' => 'This aircraft is not owned by your current airline.']);
            }

            // All checks passed, so create the flight.
            Flight::create($validated + ['airline_id' => $currentActiveAirline->id, 'pilot_id' => auth()->user()->id]);

            // And redirect the user.
            return redirect()->route('flightlist');
        }

        // Get all available online networks to display in the select
        $prefill_select_online_network = OnlineNetwork::query()->get();

        // Get all aircraft of the active airline for the select. This returns the models and we can access the properties of them in the view.
        $prefill_select_aircraft = $currentActiveAirline->aircraft;

        return view('flights.add', [ 'prefill_online_network' => $prefill_select_online_network,
                                     'prefill_aircraft' => $prefill_select_aircraft ]);
    }

    public function view(Flight $flight) {
        $currentActiveAirline = Session::get('activeairline');

        //Check if users airline is equal to the flights airline
        if($currentActiveAirline->id !== $flight->airline->id) {
            return redirect()->route('dashboard')->with('error', 'You tried to view a flight of another airline.');
        } else {
            return view('flights.detail', ['flight' => $flight ]);
        }
    }
}
