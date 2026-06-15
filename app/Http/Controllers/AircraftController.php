<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AircraftController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $currentActiveAirline = $request->session()->get('activeairline');

        if($request->getMethod() == "POST"){

            // If user tries to send POST request, although he has no permission, redirect him instantly.
            if(!auth()->user()->can('add aircraft')){
                return redirect()->route('dashboard')->with('error', 'You did something nasty!');
            }

            // Validate form input
            $validated = $request->validate([
                'registration' => 'required|max:9|regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{3,5}$/i',
                'manufacturer' => 'required|alphanum',
                'model' => 'required',
                'current_loc' => 'required|max:4',
                'remarks' => 'alphanum|nullable',
            ]);

            // Check if user given airport exists, if not throw an exception.
            if (!Airport::find($request->post('current_loc'))) {
                throw ValidationException::withMessages(['current_loc' => 'This airport could not be found in the database.']);
            }

            // Check if user given aircraft exists for the same airline and status = active. If not, throw exception.
            if (Aircraft::where('active', 1)->where('registration', '=', $request->post('registration'))->where('used_by', '=', $currentActiveAirline->id)->count() >= 1) {
                throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.']);
            } else {
                Aircraft::create($validated + ['used_by' => $currentActiveAirline->id]);
            }
        }

        // Base query with airline and search filters
        $fleetQuery = Aircraft::query()
            ->where('used_by', '=', $currentActiveAirline->id);

        if ($search = $request->get('search')) {
            $fleetQuery->where(function($q) use ($search) {
                $q->where('registration', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('current_loc', 'LIKE', "%{$search}%");
            });
        }

        $maxEntries = $fleetQuery->count();

        // Database driver-independent duration seconds SQL
        $driver = \DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $durationSecondsSql = "(strftime('%s', flights.blockon) - strftime('%s', flights.blockoff))";
        } else {
            $durationSecondsSql = "TIMESTAMPDIFF(SECOND, flights.blockoff, flights.blockon)";
        }

        $fleetQuery->select('aircraft.*')
            ->selectRaw("COALESCE((
                SELECT SUM($durationSecondsSql)
                FROM flights
                WHERE flights.aircraft_id = aircraft.id AND flights.status_id = 2
            ), 0) as logged_seconds");

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        switch ($sortBy) {
            case 'registration':
                $fleetQuery->orderBy('registration', $sortOrder);
                break;
            case 'type':
                $fleetQuery->orderBy('manufacturer', $sortOrder)
                           ->orderBy('model', $sortOrder);
                break;
            case 'current_loc':
                $fleetQuery->orderBy('current_loc', $sortOrder);
                break;
            case 'logged_hours':
                $fleetQuery->orderBy('logged_seconds', $sortOrder);
                break;
            case 'active':
                $fleetQuery->orderBy('active', $sortOrder);
                break;
            default:
                $fleetQuery->orderBy('created_at', 'desc');
                break;
        }

        $fleetQuery->orderBy('id', 'desc');

        $limit = max((int)env('FLEET_PAGE_LIMIT', 15), 1);
        $maxPages = (int)ceil($maxEntries / $limit);
        if ($maxPages < 1) {
            $maxPages = 1;
        }
        $page = (int)$request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page - 1) * $limit;

        $fleet = $fleetQuery
            ->offset($offset)
            ->limit($limit)
            ->get();

        return view('fleet.index', ['fleet' => $fleet, 'maxPages' => $maxPages, 'currentPage' => $page]);
    }

    public function edit(Request $request, Aircraft $aircraft) {
        // Authorize using AircraftPolicy
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('dashboard')->with('error', 'You did something nasty!');
        }

        // Get the current active airline
        $currentActiveAirline = $request->session()->get('activeairline');

        // Check if the request is a POST
        if($request->getMethod() == "POST"){
            // Validate input
            $validated = $request->validate([
                'registration' => 'required|uppercase|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'remarks' => 'nullable',
            ]);

            // Konvertiert "on" zu true, und das Fehlen des Feldes automatisch zu false
            $finalStatus = $request->boolean('active');

            // Actually change the values
            $targetAircraft = Aircraft::find($aircraft->id);
            $targetAircraft->registration = $request->post('registration');
            $targetAircraft->used_by = $currentActiveAirline->id;
            $targetAircraft->manufacturer = $request->post('manufacturer');
            $targetAircraft->model = $request->post('model');
            $targetAircraft->active = $finalStatus; // Setzt jetzt zuverlässig true oder false
            $targetAircraft->remarks = $request->post('remarks');

            // If we notice, that the registration has changed, we need to make a check if there is an aircraft with the same tail number already active
            if ($targetAircraft->isDirty('registration') || $targetAircraft->isDirty('active')) {

                $existingAircraft = Aircraft::where('registration', $request->post('registration'))
                    ->where('used_by', $currentActiveAirline->id)
                    ->where('id', '<>', $aircraft->id) // Exclude current aircraft
                    ->where('active', true)
                    ->exists();

                if ($existingAircraft) {
                    throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exists in this airline. Please set the aircraft inactive or choose another tail number.']);
                }
            }
            
            // Save the changes.
            $targetAircraft->save();

            // And boom: Back to fleetmanager :)
            return redirect()->route('fleetmanager');
        }

        // If we just get a GET request, display the manager.
        return view('fleet.edit', ['aircraft' => $aircraft ]);
    }

    public function view(Aircraft $aircraft) {
        // Authorize using AircraftPolicy
        if (request()->user()->cannot('view', $aircraft)) {
            return redirect()->route('dashboard')->with('error', 'The aircraft you tried to view is not owned by the current active airline.');
        }

        // Get the current active airline
        $currentActiveAirline = session()->get('activeairline');

        // Get lat and lon for position view
        $curLat = $aircraft->location->latitude_deg;
        $curLon = $aircraft->location->longitude_deg;

        // Fetch last 5 accepted flights for this aircraft
        $lastFlights = $aircraft->flights()
            ->where('status_id', 2)
            ->with(['departure_airport', 'arrival_airport', 'pilot'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return view('fleet.detail', [
            'aircraft' => $aircraft,
            'lon' => $curLon,
            'lat' => $curLat,
            'lastFlights' => $lastFlights
        ]);
    }
}

