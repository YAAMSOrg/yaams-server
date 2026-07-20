<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Support\ActivityLevel;
use App\Support\MapBounds;

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


        // Base query with airline and search filters
        $fleetQuery = Aircraft::query()
            ->where('used_by', '=', $currentActiveAirline->id);

        // Retired aircraft are hidden from the fleet list unless explicitly requested.
        $showRetired = $request->boolean('show_retired');
        if (!$showRetired) {
            $fleetQuery->notRetired();
        }

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
            case 'status':
                $fleetQuery->orderBy('status', $sortOrder);
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

        // Fleet-location map data: the full active-airline fleet (retired aircraft
        // excluded), independent of the table's search/pagination, grouped by airport.
        [$mapMarkers, $mapView, $aircraftWithoutLocation] = $this->fleetMapData($currentActiveAirline);

        return view('fleet.index', [
            'fleet' => $fleet,
            'maxPages' => $maxPages,
            'currentPage' => $page,
            'showRetired' => $showRetired,
            'mapMarkers' => $mapMarkers,
            'mapCenter' => $mapView['center'],
            'mapZoom' => $mapView['zoom'],
            'aircraftWithoutLocation' => $aircraftWithoutLocation,
        ]);
    }

    /**
     * Build the Fleet Location map payload for an airline: one marker per airport
     * (aircraft grouped together, popup listing them), the framing center/zoom, and
     * a count of aircraft whose current location could not be resolved.
     *
     * @return array{0: array<int, array{lat: float, long: float, info: string}>, 1: array{center: array{lat: float, long: float}, zoom: int}, 2: int}
     */
    private function fleetMapData(Airline $airline): array
    {
        $aircraft = Aircraft::with('location')
            ->where('used_by', $airline->id)
            ->notRetired()
            ->get();

        $markers = [];
        $points = [];

        // Skip (but count) aircraft with an unresolved/invalid current_loc, then
        // group the rest by airport so each airport yields a single marker.
        $located = $aircraft->filter(fn ($a) => $a->location !== null);
        $withoutLocation = $aircraft->count() - $located->count();
        $groups = $located->groupBy(fn ($a) => $a->location->icao_code);

        foreach ($groups as $parked) {
            $airport = $parked->first()->location;
            $points[] = ['lat' => (float) $airport->latitude_deg, 'long' => (float) $airport->longitude_deg];
            $markers[] = [
                'lat' => (float) $airport->latitude_deg,
                'long' => (float) $airport->longitude_deg,
                'info' => view('fleet._map_popup', [
                    'airport' => $airport,
                    'aircraft' => $parked,
                ])->render(),
            ];
        }

        return [$markers, MapBounds::fit($points), $withoutLocation];
    }

    public function create(Request $request) {
        $currentActiveAirline = $request->session()->get('activeairline');

        if (!auth()->user()->isManagerOf($currentActiveAirline)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to add aircraft to this airline.');
        }

        if ($request->getMethod() == "POST") {
            $validated = $request->validate([
                'registration' => 'required|max:9|regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{3,5}$/i|uppercase',
                'manufacturer' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'engine_type' => 'required|string|max:100',
                'satcom' => 'boolean',
                'winglets' => 'boolean',
                'selcal' => 'nullable|string|max:5|regex:/^[A-Z]{2}-?[A-Z]{2}$/i',
                'hex_code' => 'nullable|string|size:6|regex:/^[A-F0-9]{6}$/i',
                'msn' => 'nullable|digits_between:1,6',
                'mtow' => 'nullable|integer|min:0|max:1000000',
                'mzfw' => 'nullable|integer|min:0|max:1000000',
                'mlw' => 'nullable|integer|min:0|max:1000000',
                'remarks' => 'nullable|string|max:1000',
                'current_loc' => 'required|max:4',
            ]);

            $validated['satcom'] = $request->boolean('satcom');
            $validated['winglets'] = $request->boolean('winglets');
            
            if (isset($validated['selcal'])) {
                $validated['selcal'] = strtoupper($validated['selcal']);
            }
            if (isset($validated['hex_code'])) {
                $validated['hex_code'] = strtoupper($validated['hex_code']);
            }

            if (!Airport::find($request->post('current_loc'))) {
                throw ValidationException::withMessages(['current_loc' => 'This airport could not be found in the database.']);
            }

            $existingAircraft = Aircraft::where('status', Aircraft::STATUS_ACTIVE)
                ->where('registration', $validated['registration'])
                ->where('used_by', $currentActiveAirline->id)
                ->exists();

            if ($existingAircraft) {
                throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exists in this airline. Please set the aircraft inactive or choose another tail number.']);
            }

            Aircraft::create($validated + ['used_by' => $currentActiveAirline->id]);

            return redirect()->route('fleetmanager')->with('success', 'Aircraft registered successfully.');
        }

        $manufacturers = Aircraft::distinct()->orderBy('manufacturer')->pluck('manufacturer');
        $models = Aircraft::distinct()->orderBy('model')->pluck('model');
        $engines = Aircraft::distinct()->whereNotNull('engine_type')->where('engine_type', '<>', '')->orderBy('engine_type')->pluck('engine_type');

        return view('fleet.create', [
            'manufacturers' => $manufacturers,
            'models' => $models,
            'engines' => $engines
        ]);
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
                'registration' => 'required|max:9|regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{3,5}$/i|uppercase',
                'manufacturer' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'engine_type' => 'required|string|max:100',
                'satcom' => 'boolean',
                'winglets' => 'boolean',
                'selcal' => 'nullable|string|max:5|regex:/^[A-Z]{2}-?[A-Z]{2}$/i',
                'hex_code' => 'nullable|string|size:6|regex:/^[A-F0-9]{6}$/i',
                'msn' => 'nullable|digits_between:1,6',
                'mtow' => 'nullable|integer|min:0|max:1000000',
                'mzfw' => 'nullable|integer|min:0|max:1000000',
                'mlw' => 'nullable|integer|min:0|max:1000000',
                'remarks' => 'nullable|string|max:1000',
            ]);

            // Convert to boolean and format strings. The toggle only moves between the two
            // reversible states - retiring an aircraft is a separate, dedicated action.
            $finalStatus = $request->boolean('active') ? Aircraft::STATUS_ACTIVE : Aircraft::STATUS_INACTIVE;
            $satcom = $request->boolean('satcom');
            $winglets = $request->boolean('winglets');
            $selcal = $request->filled('selcal') ? strtoupper($request->post('selcal')) : null;
            $hex_code = $request->filled('hex_code') ? strtoupper($request->post('hex_code')) : null;

            // Actually change the values
            $targetAircraft = Aircraft::find($aircraft->id);
            $targetAircraft->registration = $request->post('registration');
            $targetAircraft->used_by = $currentActiveAirline->id;
            $targetAircraft->manufacturer = $request->post('manufacturer');
            $targetAircraft->model = $request->post('model');
            $targetAircraft->engine_type = $request->post('engine_type');
            $targetAircraft->satcom = $satcom;
            $targetAircraft->winglets = $winglets;
            $targetAircraft->selcal = $selcal;
            $targetAircraft->hex_code = $hex_code;
            $targetAircraft->msn = $request->post('msn');
            $targetAircraft->mtow = $request->post('mtow');
            $targetAircraft->mzfw = $request->post('mzfw');
            $targetAircraft->mlw = $request->post('mlw');
            $targetAircraft->status = $finalStatus;
            $targetAircraft->remarks = $request->post('remarks');

            // If we notice that the registration has changed or active status, we need to check if there is another active aircraft with same tail number
            if ($targetAircraft->isDirty('registration') || $targetAircraft->isDirty('status')) {

                $existingAircraft = Aircraft::where('registration', $request->post('registration'))
                    ->where('used_by', $currentActiveAirline->id)
                    ->where('id', '<>', $aircraft->id) // Exclude current aircraft
                    ->where('status', Aircraft::STATUS_ACTIVE)
                    ->exists();

                if ($existingAircraft) {
                    throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exists in this airline. Please set the aircraft inactive or choose another tail number.']);
                }
            }
            
            // Save the changes.
            $targetAircraft->save();

            // And boom: Back to fleetmanager :)
            return redirect()->route('fleetmanager')->with('success', 'Aircraft updated successfully.');
        }

        // If we just get a GET request, display the manager.
        $aircraft->load(['approvedImages', 'pendingImages.uploader']);
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

        // Screenshot gallery: approved shots (primary first) plus the pending
        // review queue (with uploader for display/authorization).
        $aircraft->load(['approvedImages', 'pendingImages.uploader']);

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

    public function retire(Request $request, Aircraft $aircraft) {
        // Authorize using AircraftPolicy - denies non-managers and already-retired aircraft.
        if ($request->user()->cannot('retire', $aircraft)) {
            return redirect()->route('fleetmanager')->with('error', 'You cannot retire this aircraft.');
        }

        $validated = $request->validate([
            'retired_reason' => 'required|string|max:255',
        ]);

        // Permanent, one-way transition: the aircraft can no longer fly and cannot be reactivated.
        $aircraft->status = Aircraft::STATUS_RETIRED;
        $aircraft->retired_at = now();
        $aircraft->retired_reason = $validated['retired_reason'];
        $aircraft->save();

        activity()
            ->causedBy($request->user())
            ->performedOn($aircraft)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('aircraft_retired')
            ->log('Retired aircraft ' . $aircraft->registration);

        return redirect()->route('fleetmanager')->with('success', 'Aircraft ' . $aircraft->registration . ' has been retired.');
    }
}

