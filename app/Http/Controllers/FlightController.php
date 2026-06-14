<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\OnlineNetwork;
use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Events\FlightFiled;

class FlightController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function displayAllFlights()
    {
        $flights = Flight::query()->orderBy("created_at", "DESC")->get();

        return view("flights.list", ["flights" => $flights]);
    }

    public function displayFlightsForUser(Request $request)
    {
        $current_auth_user_id = auth()->id();
        $currentActiveAirline = session()->get('activeairline'); // Kleingeschrieben "session()" nutzen oder Session::get()

        // 1. Limit sicherstellen (Fallback auf 10, falls env() null oder nicht numerisch ist)
        $limit = (int) env('FLIGHT_PAGE_LIMIT', 10);
        if ($limit < 1) {
            $limit = 1;
        }

        // 2. Einträge zählen
        $maxEntries = Flight::query()
            ->where('pilot_id', $current_auth_user_id)
            ->where('airline_id', $currentActiveAirline->id)
            ->count();

        // 3. Seiten berechnen – Sicherstellen, dass maxPages mindestens 1 ist!
        $maxPages = (int) ceil($maxEntries / $limit);
        if ($maxPages < 1) {
            $maxPages = 1;
        }

        // 4. Aktuelle Seite validieren
        $page = (int) $request->get('page', 1);
        $page = min(max(1, $page), $maxPages);

        // 5. Offset berechnen
        $offset = ($page - 1) * $limit;

        // 6. Daten holen
        $flights = Flight::query()
            ->where('pilot_id', $current_auth_user_id)
            ->where('airline_id', $currentActiveAirline->id)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return view('flights.list', [
            'flights'     => $flights,
            'maxPages'    => $maxPages,
            'currentPage' => $page
        ]);
    }

    public function addFlight(Request $request)
    {
        $currentActiveAirline = Session()->get("activeairline");

        //Reload airline since we sometimes saw
        $tempAirlineID = $currentActiveAirline->id;
        $request->session()->forget("activeairline");
        $tempAirline = Airline::find($tempAirlineID);
        $request->session()->put("activeairline", $tempAirline);

        if ($request->getMethod() == "POST") {
            $validated = $request->validate([
                "flightnumber" => "numeric|digits_between:1,4|required",
                "departure_icao" => "alpha|max:4|required",
                "arrival_icao" => "alpha|max:4|required",
                "aircraft_id" => "numeric|required",
                "callsign" => [
                    "required",
                    "max:4", // Angepasst an dein HTML maxlength="4"
                    // ICAO Standard: 1-4 Ziffern, optional gefolgt von max 2 Buchstaben
                    "regex:/^[0-9]{1,4}[A-Za-z]{0,2}$/",
                ],
                "crzalt" => "numeric|max:50000|digits_between:1,5|required",
                "blockoff" => "required",
                "blockon" => "required",
                "burned_fuel" => "numeric|required",
                "route" => "required",
                "online_network_id" => "required",
                "remarks" => [
                    "nullable", 
                    // Erlaubt: Buchstaben (inkl. Umlaute), Leerzeichen, Zahlen, Punkt, Komma, Bindestrich
                    "regex:/^[\pL\s\d\.\,\-]+$/u"
                ],
            ]);

            // Check if user given airport exists, if not throw an exception. We need to do this on the two fields, to display the error.
            if (!Airport::find($request->post("departure_icao"))) {
                throw ValidationException::withMessages([
                    "departure_icao" =>
                        "This airport could not be found in the database.",
                ]);
            }
            if (!Airport::find($request->post("arrival_icao"))) {
                throw ValidationException::withMessages([
                    "arrival_icao" =>
                        "This airport could not be found in the database.",
                ]);
            }

            // Check if the aircraft is indeed part of the active airline AND is active
            if (Aircraft::query()
                    ->where("id", "=", $request->post("aircraft_id"))
                    ->where("used_by", "=", $currentActiveAirline->id)
                    ->where("active", true)
                    ->count() == 0) {
                throw ValidationException::withMessages([
                    "aircraft_id" => "This aircraft is not available or not owned by your current airline.",
                ]);
            }

            // All checks passed, so create the flight.
            Flight::create(
                $validated + [
                    "airline_id" => $currentActiveAirline->id,
                    "pilot_id" => auth()->user()->id,
                ]
            );

            //This does not work yet?
            //            dd(new FlightFiled(auth()->user()));
            //event(new FlightFiled(auth()->user()));

            // And redirect the user.
            return redirect()->route("flightlist");
        }

        // Get all available online networks to display in the select
        $prefill_select_online_network = OnlineNetwork::query()->get();

        // Get all aircraft of the active airline for the select. This returns the models and we can access the properties of them in the view.
        $prefill_select_aircraft = $currentActiveAirline->activeAircraft;

        return view("flights.add", [
            "prefill_online_network" => $prefill_select_online_network,
            "prefill_aircraft" => $prefill_select_aircraft,
        ]);
    }

    public function view(Flight $flight)
    {
        $currentActiveAirline = Session::get("activeairline");

        //Check if users airline is equal to the flights airline
        if ($currentActiveAirline->id !== $flight->airline->id) {
            return redirect()
                ->route("dashboard")
                ->with(
                    "error",
                    "You tried to view a flight of another airline."
                );
        } else {
            return view("flights.detail", ["flight" => $flight]);
        }
    }

    public function listReviewFlights()
    {
        $currentActiveAirline = Session::get("activeairline");
        $current_auth_user_id = auth()->user()->id;

        $flights = Flight::query()
            ->where("pilot_id", $current_auth_user_id)
            ->where("airline_id", $currentActiveAirline->id)
            ->where("status_id", "=", "1")
            ->orderBy("created_at", "DESC")
            ->get();

        return view("flights.review", ["flights" => $flights]);
    }

    public function acceptFlight(Flight $flight)
    {
        $currentActiveAirline = Session::get("activeairline");

        // Is the flight part of the active airline?
        if ($currentActiveAirline->id !== $flight->airline_id) {
            return redirect()
                ->route("dashboard")
                ->with("error", "You tried to review a flight of another airline.");
        }

        // Status auf 'Accepted' setzen (Angenommen, Status ID 2 steht für "Accepted")
        // Falls deine IDs in der DB anders definiert sind, passe die '2' entsprechend an.
        $flight->status_id = 2; 
        $flight->save();

        return redirect()->route('flightreviewindex')->with('success', 'Flight successfully approved.');
    }

    public function rejectFlight(Flight $flight)
    {
        $currentActiveAirline = Session::get("activeairline");

        // Is the flight part of the active airline?
        if ($currentActiveAirline->id !== $flight->airline_id) {
            return redirect()
                ->route("dashboard")
                ->with("error", "You tried to review a flight of another airline.");
        }

        // Status auf 'Rejected' / 'Denied' setzen (Angenommen, Status ID 3 steht für "Rejected")
        // Falls deine IDs in der DB anders definiert sind, passe die '3' entsprechend an.
        $flight->status_id = 3;
        $flight->save();

        return redirect()->route('flightreviewindex')->with('success', 'Flight has been rejected.');
    }
}
