<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFlightRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Http\Resources\V1\FlightResource;
use App\Models\Flight;
use App\Models\Airline;
use App\Events\FlightFiled;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepRejected;
use App\Support\ActivityLevel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

/**
 * @group Flights
 *
 * PIREPs (pilot reports / flight logs) for an airline. Filing, listing, and -
 * for reviewers - the review queue plus accept/reject.
 */
class FlightAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List my flights
     *
     * List the authenticated pilot's own flights for a specific airline.
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @queryParam status_id integer Filter by flight status: 1 = Pending, 2 = Accepted, 3 = Rejected. Example: 2
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 42,
     *       "callsign": "421",
     *       "flightNumber": "421",
     *       "fullFlightNumber": "EV421",
     *       "fullIcaoCallsign": "EVA421",
     *       "departureIcao": "EDDF",
     *       "arrivalIcao": "EGLL",
     *       "cruiseAltitude": 36000,
     *       "blockOff": "2026-07-11 10:00:00",
     *       "blockOn": "2026-07-11 11:30:00",
     *       "duration": "01:30",
     *       "burnedFuel": 4200,
     *       "route": "SOVAT UL610 KONAN",
     *       "onlineNetwork": 1,
     *       "status": {"id": 2, "name": "Accepted"},
     *       "remarks": null,
     *       "rejectionRemarks": null,
     *       "createdAt": "2026-07-11T11:35:00.000000Z",
     *       "updatedAt": "2026-07-11T11:40:00.000000Z"
     *     }
     *   ]
     * }
     * @response 403 {"message": "You are not a member of this airline."}
     */
    public function index(Request $request, Airline $airline)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        $user = $request->user();
        $query = Flight::with(['airline', 'aircraft', 'pilot', 'status'])
            ->where('pilot_id', $user->id)
            ->where('airline_id', $airline->id);

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        return new FlightCollection($query->orderBy('created_at', 'DESC')->paginate(15));
    }

    /**
     * File a PIREP
     *
     * Submit a new PIREP for the airline. Membership, airport/aircraft
     * existence and the location-continuity rule are enforced by
     * StoreFlightRequest. If the airline requires review the flight is created
     * Pending (status 1) and reviewers are notified; otherwise it is Accepted
     * (status 2) immediately.
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @bodyParam flightnumber integer required Numeric flight number, 1-4 digits. Example: 421
     * @bodyParam departure_icao string required Departure airport ICAO (must exist; uppercased). Example: EDDF
     * @bodyParam arrival_icao string required Arrival airport ICAO (must exist; uppercased). Example: EGLL
     * @bodyParam aircraft_id integer required ID of an active aircraft owned by the airline. Example: 7
     * @bodyParam callsign string required Radio callsign, 1-4 digits optionally followed by up to 2 letters. Example: 421
     * @bodyParam crzalt integer required Cruise altitude in feet, max 50000. Example: 36000
     * @bodyParam blockoff string required Block-off time (UTC), format Y-m-d H:i:s. Example: 2026-07-11 10:00:00
     * @bodyParam blockon string required Block-on time (UTC), format Y-m-d H:i:s. Must be after blockoff; flight duration may not exceed 26 hours. Example: 2026-07-11 11:30:00
     * @bodyParam burned_fuel integer required Fuel burned, in the airline's unit (min 1, max 600000). Example: 4200
     * @bodyParam route string required Filed route string. Example: SOVAT UL610 KONAN
     * @bodyParam online_network_id integer required Online network ID (must exist in online_networks). Example: 1
     * @bodyParam remarks string Optional remarks (letters, digits, spaces, . , -). Example: Smooth flight.
     *
     * @response 201 {
     *   "data": {
     *     "id": 42,
     *     "callsign": "421",
     *     "flightNumber": "421",
     *     "fullFlightNumber": "EV421",
     *     "fullIcaoCallsign": "EVA421",
     *     "departureIcao": "EDDF",
     *     "arrivalIcao": "EGLL",
     *     "cruiseAltitude": 36000,
     *     "blockOff": "2026-07-11 10:00:00",
     *     "blockOn": "2026-07-11 11:30:00",
     *     "duration": "01:30",
     *     "burnedFuel": 4200,
     *     "route": "SOVAT UL610 KONAN",
     *     "onlineNetwork": 1,
     *     "status": {"id": 1, "name": "Pending"},
     *     "remarks": null,
     *     "rejectionRemarks": null,
     *     "createdAt": "2026-07-11T11:35:00.000000Z",
     *     "updatedAt": "2026-07-11T11:35:00.000000Z"
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The departure icao field is required.", "errors": {"departure_icao": ["The departure icao field is required."]}}
     */
    public function store(StoreFlightRequest $request, Airline $airline)
    {
        $user = $request->user();
        $validated = $request->validated();
        $aircraft = $request->aircraft();

        $flight = Flight::create($validated + [
            'pilot_id' => $user->id,
            'airline_id' => $airline->id,
        ]);

        // Location continuity: the airframe has physically moved to the arrival airport
        if ($airline->location_continuity) {
            $aircraft->update(['current_loc' => $validated['arrival_icao']]);
        }

        if ($airline->require_pirep_review) {
            event(new FlightFiled($flight));
        } else {
            // Auto-accept: the airline does not require PIREP review, so nothing is dispatched to reviewers
            $flight->status_id = 2;
            $flight->save();
        }

        activity()
            ->causedBy($user)
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_filed')
            ->log('Filed PIREP ' . $flight->full_flight_number);

        return (new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * List the review queue
     *
     * List pending PIREPs (status 1) for an airline. Requires the per-airline
     * Dispatcher or Manager role.
     *
     * @urlParam airline int required The airline ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 43,
     *       "callsign": "422",
     *       "flightNumber": "422",
     *       "fullFlightNumber": "EV422",
     *       "fullIcaoCallsign": "EVA422",
     *       "departureIcao": "EGLL",
     *       "arrivalIcao": "EDDF",
     *       "cruiseAltitude": 37000,
     *       "blockOff": "2026-07-11 13:00:00",
     *       "blockOn": "2026-07-11 14:25:00",
     *       "duration": "01:25",
     *       "burnedFuel": 4100,
     *       "route": "DET L6 KONAN",
     *       "onlineNetwork": 1,
     *       "status": {"id": 1, "name": "Pending"},
     *       "remarks": null,
     *       "rejectionRemarks": null,
     *       "createdAt": "2026-07-11T14:30:00.000000Z",
     *       "updatedAt": "2026-07-11T14:30:00.000000Z"
     *     }
     *   ]
     * }
     * @response 403 {"message": "You do not have permission to review flights for this airline."}
     */
    public function reviewList(Request $request, Airline $airline)
    {
        // Per-airline Dispatcher/Manager role - same rule as the web review pages.
        if (! $request->user()->canReviewFlightsFor($airline)) {
            return response()->json(['message' => 'You do not have permission to review flights for this airline.'], 403);
        }

        $flights = Flight::with(['airline', 'aircraft', 'pilot', 'status'])
            ->where('airline_id', $airline->id)
            ->where('status_id', 1)
            ->orderBy('created_at', 'DESC')
            ->get();

        return new FlightCollection($flights);
    }

    /**
     * Accept a PIREP
     *
     * Mark a pending PIREP as Accepted (status 2) and notify the pilot.
     * Requires the "review flight" permission for the flight's airline.
     *
     * @urlParam flight int required The flight ID. Example: 43
     *
     * @response 200 {
     *   "data": {
     *     "id": 43,
     *     "callsign": "422",
     *     "flightNumber": "422",
     *     "fullFlightNumber": "EV422",
     *     "departureIcao": "EGLL",
     *     "arrivalIcao": "EDDF",
     *     "status": {"id": 2, "name": "Accepted"},
     *     "rejectionRemarks": null
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     */
    public function accept(Request $request, Flight $flight)
    {
        $this->authorize('review', $flight);

        $flight->status_id = 2;
        $flight->save();

        // Notify the pilot (in-app + email). Channels live in PirepAccepted::via().
        Notification::send($flight->pilot, new PirepAccepted($flight));

        activity()
            ->causedBy($request->user())
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_accepted')
            ->log('Accepted PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }

    /**
     * Reject a PIREP
     *
     * Mark a PIREP as Rejected (status 3) and notify the pilot. If location
     * continuity is on and the flight was still pending, the aircraft is moved
     * back to the flight's departure. Requires the "review flight" permission.
     *
     * @urlParam flight int required The flight ID. Example: 43
     *
     * @bodyParam rejection_remarks string Reason shown to the pilot. Example: Cruise altitude above aircraft ceiling.
     *
     * @response 200 {
     *   "data": {
     *     "id": 43,
     *     "callsign": "422",
     *     "flightNumber": "422",
     *     "fullFlightNumber": "EV422",
     *     "departureIcao": "EGLL",
     *     "arrivalIcao": "EDDF",
     *     "status": {"id": 3, "name": "Rejected"},
     *     "rejectionRemarks": "Cruise altitude above aircraft ceiling."
     *   }
     * }
     * @response 403 {"message": "This action is unauthorized."}
     */
    public function reject(Request $request, Flight $flight)
    {
        $this->authorize('review', $flight);

        $wasPending = $flight->status_id === 1;

        $flight->status_id = 3;

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
        Notification::send($flight->pilot, new PirepRejected($flight));

        activity()
            ->causedBy($request->user())
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_rejected')
            ->log('Rejected PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }
}
