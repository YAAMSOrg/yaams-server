<?php

namespace App\Http\Controllers;

use App\Models\Flight;


class DashboardController extends Controller
{
    /** Relations every flight row on the dashboard renders. */
    private const FLIGHT_RELATIONS = ['airline', 'aircraft', 'status', 'departure_airport', 'arrival_airport'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $currentActiveAirline = session()->get('activeairline');

        if (!$currentActiveAirline) {
            return redirect()->route('portal');
        }

        $user = auth()->user();

        // The pilot's own latest PIREPs in any state, so a pending or rejected
        // report is visible right after login.
        $myFlights = Flight::query()
        ->with(self::FLIGHT_RELATIONS)
        ->where('airline_id', '=', $currentActiveAirline->id)
        ->where('pilot_id', '=', $user->id)
        ->latest()
        ->orderByDesc('id')
        ->limit(5)
        ->get();

        $airlineFlights = Flight::query()
        ->with([...self::FLIGHT_RELATIONS, 'pilot'])
        ->where('airline_id', '=', $currentActiveAirline->id)
        ->where('status_id', '=', 2)
        ->latest()
        ->orderByDesc('id')
        ->limit(5)
        ->get();

        $flightCount = $user->logged_flights($currentActiveAirline);
        $flightMinutes = $user->logged_minutes($currentActiveAirline);

        $notams = $currentActiveAirline->notams()->with('author')->active()->latest()->get();

        return view('dashboard.index', [
            'flights' => $airlineFlights,
            'my_flights' => $myFlights,
            'flight_count' => $flightCount,
            'flight_minutes' => $flightMinutes,
            'notams' => $notams,
        ]);
    }
}
