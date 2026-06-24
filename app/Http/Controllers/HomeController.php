<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $airlines = Airline::withCount(['users', 'flights' => fn($q) => $q->where('status_id', 2)])
            ->where('active', true)
            ->get();

        $acceptedFlights = Flight::where('status_id', 2)->get();

        $stats = [
            'airlines' => $airlines->count(),
            'pilots'   => User::count(),
            'flights'  => $acceptedFlights->count(),
            'hours'    => (int) floor($acceptedFlights->sum('flight_duration_minutes') / 60),
        ];

        return view('home.index', compact('airlines', 'stats'));
    }
}
