<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aircraft;
use App\Models\Flight;
use App\Models\Airline;


class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $currentActiveAirline = session()->get('activeairline');

        // Alternative approach:
        // $airlineFlights = $currentActiveAirline->flights;

        $airlineFlights = Flight::query()
        ->orderBy('created_at', 'DESC')
        ->where('airline_id', '=', $currentActiveAirline->id )
        ->where('status_id', '=', 2)
        ->limit(5)
        ->get();

        $flightCount = auth()->user()->logged_flights($currentActiveAirline);
        $flightHours = auth()->user()->logged_hours($currentActiveAirline);

        return view('dashboard.index', ['flights' => $airlineFlights, 'flight_count' => $flightCount, 'flight_hours' => $flightHours]);
    }
}
