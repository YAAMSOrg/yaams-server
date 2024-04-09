<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airport;

use Illuminate\Http\Request;
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
                'registration' => 'required|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'current_loc' => 'required|max:4',
                'remarks' => 'nullable',
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

        // This is pagination voodo.
        $limit = max(env('FLEET_PAGE_LIMIT'), 1);
        $maxEntries = Aircraft::count();
        $maxPages = (int)ceil($maxEntries/$limit);
        $page = (int)$request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page -1) * $limit;

        // This is pagination voodo.
        $fleet = Aircraft::query()
        ->orderBy('created_at', 'DESC')
        ->where('used_by', '=', $currentActiveAirline->id )
        ->offset($offset)
        ->limit($limit)
        ->get();

        return view('fleet.index', ['fleet' => $fleet, 'maxPages' => $maxPages, 'currentPage' => $page]);
    }

    public function edit(Request $request, Aircraft $aircraft) {
        // Get the current active airline
        $currentActiveAirline = $request->session()->get('activeairline');
    
        //Check if users airline owns the aircraft
        if(!$currentActiveAirline->id = $aircraft->airline->id) {
            return redirect()->route('dashboard')->with('error', 'You did something nasty!');
        }
    
        /**
         * Since we get "on" or "off" from the form checkbox element, we need to convert this into usable values. If we get "on", we can set $gotStatus to true.
         */
        $gotStatus = $request->post('active');
        if($gotStatus == "on"){
            $finalStatus = true;
        } else {
            $finalStatus = false;
        }

        // Check if the request is a POST
        if($request->getMethod() == "POST"){
            // Validate input
            // TODO: Is this possible to define the validation rule up above and just use it here? It's nearly the same anyway...
            $validated = $request->validate([
                'registration' => 'required|uppercase|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'remarks' => 'nullable',
            ]);

            // Actually change the values
            $targetAircraft = Aircraft::find($aircraft->id);
            $targetAircraft->registration = $request->post('registration');
            $targetAircraft->used_by = $currentActiveAirline->id;
            $targetAircraft->manufacturer = $request->post('manufacturer');
            $targetAircraft->model = $request->post('model');
            $targetAircraft->active = $finalStatus;
            $targetAircraft->remarks = $request->post('remarks');
    
            // If we notice, that the registration has changed, we need to make a check if there is an aircraft with the same tail number already active, since we can't have the same tail number active twice.
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
        return view('fleet.detail', ['aircraft' => $aircraft ]);
    }
}

