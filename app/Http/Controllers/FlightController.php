<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;

class FlightController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        return view('flights.add');
    }

    public function listFlights() {
        $flights = Flight::query()->orderBy('created_at', 'DESC')->get();
        return view('flights.list', ['flights' => $flights]);
    }

    public function store(Request $request){
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
        dump($validated);


        return view('flights.add');
    }
}
