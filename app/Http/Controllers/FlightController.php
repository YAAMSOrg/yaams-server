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
use App\Events\FlightFiled;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepRejected;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Support\ActivityLevel;

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
        $currentActiveAirline = session()->get('activeairline');

        // 1. Base query
        $flightsQuery = Flight::query()
            ->where('pilot_id', $current_auth_user_id)
            ->where('airline_id', $currentActiveAirline->id);

        // 2. Apply search
        if ($search = $request->get('search')) {
            $flightsQuery->where(function($q) use ($search, $currentActiveAirline) {
                if (is_numeric($search)) {
                    $q->where('flights.id', '=', $search);
                }
                
                $prefix = $currentActiveAirline->prefix;
                $icao = $currentActiveAirline->icao_callsign;
                $searchNumber = $search;
                if (str_starts_with(strtoupper($search), strtoupper($prefix))) {
                    $searchNumber = substr($search, strlen($prefix));
                } elseif (str_starts_with(strtoupper($search), strtoupper($icao))) {
                    $searchNumber = substr($search, strlen($icao));
                }
                $searchNumber = trim($searchNumber);
                
                if (is_numeric($searchNumber)) {
                    $q->orWhere('flights.flightnumber', '=', $searchNumber);
                }
                
                $q->orWhere('flights.callsign', 'LIKE', "%{$search}%")
                  ->orWhere('flights.departure_icao', 'LIKE', "%{$search}%")
                  ->orWhere('flights.arrival_icao', 'LIKE', "%{$search}%")
                  ->orWhereHas('aircraft', function($aq) use ($search) {
                      $aq->where('registration', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 3. Count matching entries
        $maxEntries = $flightsQuery->count();

        // 4. Select fields and Joins
        $flightsQuery->select('flights.*');

        $driver = \DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $durationSecondsSql = "(strftime('%s', flights.blockon) - strftime('%s', flights.blockoff))";
        } else {
            $durationSecondsSql = "TIMESTAMPDIFF(SECOND, flights.blockoff, flights.blockon)";
        }

        // 5. Apply sorting
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        switch ($sortBy) {
            case 'id':
                $flightsQuery->orderBy('flights.id', $sortOrder);
                break;
            case 'flightnumber':
                $flightsQuery->orderBy('flights.flightnumber', $sortOrder);
                break;
            case 'callsign':
                $flightsQuery->orderBy('flights.callsign', $sortOrder);
                break;
            case 'route':
                $flightsQuery->orderBy('flights.departure_icao', $sortOrder)
                             ->orderBy('flights.arrival_icao', $sortOrder);
                break;
            case 'duration':
                $flightsQuery->orderByRaw("$durationSecondsSql $sortOrder");
                break;
            case 'aircraft':
                $flightsQuery->leftJoin('aircraft', 'flights.aircraft_id', '=', 'aircraft.id')
                             ->orderBy('aircraft.registration', $sortOrder);
                break;
            case 'date':
                $flightsQuery->orderBy('flights.blockon', $sortOrder);
                break;
            case 'status':
                $flightsQuery->orderBy('flights.status_id', $sortOrder);
                break;
            default:
                $flightsQuery->orderBy('flights.created_at', 'desc');
                break;
        }

        $flightsQuery->orderBy('flights.id', 'desc');

        // 6. Pagination settings
        $limit = (int) env('FLIGHT_PAGE_LIMIT', 10);
        if ($limit < 1) {
            $limit = 1;
        }

        $maxPages = (int) ceil($maxEntries / $limit);
        if ($maxPages < 1) {
            $maxPages = 1;
        }

        $page = (int) $request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page - 1) * $limit;

        // 7. Get results
        $flights = $flightsQuery
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
        $currentActiveAirline = session()->get("activeairline");

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
            $aircraft = Aircraft::query()
                ->where("id", "=", $request->post("aircraft_id"))
                ->where("used_by", "=", $currentActiveAirline->id)
                ->where("status", Aircraft::STATUS_ACTIVE)
                ->first();
            if (is_null($aircraft)) {
                throw ValidationException::withMessages([
                    "aircraft_id" => "This aircraft is not available or not owned by your current airline.",
                ]);
            }

            // Location continuity: the flight must depart from where the airframe currently is
            if ($tempAirline->location_continuity
                && strtoupper($validated["departure_icao"]) !== strtoupper((string) $aircraft->current_loc)) {
                throw ValidationException::withMessages([
                    "departure_icao" => "Location continuity is enabled: " . $aircraft->registration
                        . " is currently located at " . ($aircraft->current_loc ?: "an unknown location") . ".",
                ]);
            }

            // All checks passed, so create the flight.
            $flight = Flight::create(
                $validated + [
                    "airline_id" => $currentActiveAirline->id,
                    "pilot_id" => auth()->user()->id,
                ]
            );

            // Location continuity: the airframe has physically moved to the arrival airport
            if ($tempAirline->location_continuity) {
                $aircraft->update(["current_loc" => strtoupper($validated["arrival_icao"])]);
            }

            if ($tempAirline->require_pirep_review) {
                event(new FlightFiled($flight));
            } else {
                // Auto-accept: the airline does not require PIREP review, so nothing is dispatched to reviewers
                $flight->status_id = 2;
                $flight->save();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($flight)
                ->withProperties(['level' => ActivityLevel::INFO])
                ->event('pirep_filed')
                ->log('Filed PIREP ' . $flight->full_flight_number);

            // And redirect the user.
            return redirect()->route("flightlist");
        }

        // Guard: no aircraft means the form is unusable
        if ($currentActiveAirline->activeAircraft->isEmpty()) {
            return redirect()->route('flightlist')->with(
                'error',
                'You need to add an aircraft first before adding a flight!'
            );
        }

        // Get all available online networks to display in the select
        $prefill_select_online_network = OnlineNetwork::query()->get();

        // Get all aircraft of the active airline for the select. This returns the models and we can access the properties of them in the view.
        $prefill_select_aircraft = $currentActiveAirline->activeAircraft;

        return view("flights.add", [
            "prefill_online_network" => $prefill_select_online_network,
            "prefill_aircraft" => $prefill_select_aircraft,
            "location_continuity" => $tempAirline->location_continuity,
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

    public function displayCrewActivity(Request $request)
    {
        $currentActiveAirline = $request->session()->get('activeairline');

        // Base scope: this airline's accepted flights (status_id = 2)
        $baseFeedQuery = Flight::query()
            ->where('airline_id', $currentActiveAirline->id)
            ->where('status_id', 2);

        // Manual pagination (matching the fleet listing pattern)
        $maxEntries = (clone $baseFeedQuery)->count();
        $limit = max((int) env('CREW_FEED_PAGE_LIMIT', 15), 1);
        $maxPages = (int) ceil($maxEntries / $limit);
        if ($maxPages < 1) {
            $maxPages = 1;
        }
        $page = (int) $request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page - 1) * $limit;

        $feed = (clone $baseFeedQuery)
            ->with(['pilot', 'aircraft', 'departure_airport', 'arrival_airport'])
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Leaderboard: top pilots this month by accepted-flight count
        $leaderboard = Flight::query()
            ->where('airline_id', $currentActiveAirline->id)
            ->where('status_id', 2)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('pilot_id, COUNT(*) as flights_count')
            ->groupBy('pilot_id')
            ->orderByDesc('flights_count')
            ->limit(5)
            ->with('pilot')
            ->get();

        // Leaderboard: top aircraft this month by accepted-flight count
        $aircraftLeaderboard = Flight::query()
            ->where('airline_id', $currentActiveAirline->id)
            ->where('status_id', 2)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('aircraft_id, COUNT(*) as flights_count')
            ->groupBy('aircraft_id')
            ->orderByDesc('flights_count')
            ->limit(5)
            ->with('aircraft')
            ->get();

        // Community stats
        $flightsThisWeek = Flight::query()
            ->where('airline_id', $currentActiveAirline->id)
            ->where('status_id', 2)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $activePilots = Flight::query()
            ->where('airline_id', $currentActiveAirline->id)
            ->where('status_id', 2)
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct()
            ->count('pilot_id');

        // Crew roster with per-pilot stats (sorted by flights flown)
        $roster = $currentActiveAirline->users()->get()
            ->map(function ($member) use ($currentActiveAirline) {
                return [
                    'user' => $member,
                    'role' => $member->pivot->role ?? 'Pilot',
                    'flights' => $member->logged_flights($currentActiveAirline),
                    'hours' => $member->logged_hours($currentActiveAirline),
                ];
            })
            ->sortByDesc('flights')
            ->values();

        return view('flights.activity', [
            'activeAirline' => $currentActiveAirline,
            'feed' => $feed,
            'leaderboard' => $leaderboard,
            'aircraftLeaderboard' => $aircraftLeaderboard,
            'flightsThisWeek' => $flightsThisWeek,
            'activePilots' => $activePilots,
            'crewSize' => $roster->count(),
            'airlineHours' => $roster->sum('hours'),
            'roster' => $roster,
            'maxPages' => $maxPages,
            'currentPage' => $page,
        ]);
    }

    public function listReviewFlights()
    {
        $currentActiveAirline = Session::get("activeairline");

        if (!auth()->user()->canReviewFlightsFor($currentActiveAirline)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to review flights for this airline.');
        }

        $flights = Flight::query()
            ->where("airline_id", $currentActiveAirline->id)
            ->where("status_id", "=", "1")
            ->orderBy("created_at", "DESC")
            ->get();

        return view("flights.review", ["flights" => $flights]);
    }

    public function acceptFlight(Flight $flight)
    {
        $currentActiveAirline = Session::get("activeairline");

        if (!auth()->user()->canReviewFlightsFor($currentActiveAirline)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to review flights for this airline.');
        }

        // Is the flight part of the active airline?
        if ($currentActiveAirline->id !== $flight->airline_id) {
            return redirect()
                ->route("dashboard")
                ->with("error", "You tried to review a flight of another airline.");
        }

        // Dispatchers cannot review their own PIREPs
        if ($flight->pilot_id === auth()->id()
            && !auth()->user()->isManagerOf($currentActiveAirline)
            && !auth()->user()->hasRole('Super-Admin')) {
            return redirect()->route('flightreviewindex')->with('error', 'You cannot accept your own PIREP.');
        }

        // Status auf 'Accepted' setzen (Status ID 2)
        $flight->status_id = 2; 
        $flight->save();

        // Notify the pilot (in-app + email). Channels live in PirepAccepted::via().
        NotificationFacade::send($flight->pilot, new PirepAccepted($flight));

        activity()
            ->causedBy(auth()->user())
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_accepted')
            ->log('Accepted PIREP ' . $flight->full_flight_number);

        return redirect()->route('flightreviewindex')->with('success', 'Flight successfully approved.');
    }

    public function rejectFlight(Request $request, Flight $flight)
    {
        $currentActiveAirline = session()->get("activeairline");

        if (!auth()->user()->canReviewFlightsFor($currentActiveAirline)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to review flights for this airline.');
        }

        // Is the flight part of the active airline?
        if ($currentActiveAirline->id !== $flight->airline_id) {
            return redirect()
                ->route("dashboard")
                ->with("error", "You tried to review a flight of another airline.");
        }

        // Dispatchers cannot review their own PIREPs
        if ($flight->pilot_id === auth()->id()
            && !auth()->user()->isManagerOf($currentActiveAirline)
            && !auth()->user()->hasRole('Super-Admin')) {
            return redirect()->route('flightreviewindex')->with('error', 'You cannot reject your own PIREP.');
        }

        $wasPending = $flight->status_id === 1;

        // Status auf 'Rejected' setzen (Status ID 3)
        $flight->status_id = 3;

        // Optional: Rejection Remarks speichern
        if ($request->has('rejection_remarks')) {
            $flight->rejection_remarks = $request->input('rejection_remarks');
        }

        $flight->save();

        // Location continuity: undo the movement from filing, unless a later flight has already moved the airframe on
        if ($wasPending
            && $flight->airline->location_continuity
            && strtoupper((string) $flight->aircraft->current_loc) === strtoupper($flight->arrival_icao)) {
            $flight->aircraft->update(['current_loc' => strtoupper($flight->departure_icao)]);
        }

        // Notify the pilot (in-app + email). Channels live in PirepRejected::via().
        NotificationFacade::send($flight->pilot, new PirepRejected($flight));

        activity()
            ->causedBy(auth()->user())
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_rejected')
            ->log('Rejected PIREP ' . $flight->full_flight_number);

        return redirect()->route('flightreviewindex')->with('success', 'Flight has been rejected.');
    }
}
