<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AircraftController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        if($request->getMethod() == "POST"){
            $validated = $request->validate([
                'registration' => 'required|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'current_loc' => 'required|max:4',
                'remarks' => 'nullable',
                'used_by' => 'required'
            ]);
           
            if (Aircraft::where('active', 1)->where('registration', '=', $request->post('registration'))->where('used_by', '=', $request->post('used_by'))->count() >= 1) {
                throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.']);
            } else {
                Aircraft::create($validated);
            }
        }

        $limit = max(env('FLEET_PAGE_LIMIT'), 1);
        $maxEntries = Aircraft::count();
        $maxPages = (int)ceil($maxEntries/$limit);
        $page = (int)$request->get('page', 1);
        $page = min(max(1, $page), $maxPages);
        $offset = ($page -1) * $limit;

        $fleet = Aircraft::query()
        ->orderBy('created_at', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get();

        return view('fleet.index', ['fleet' => $fleet, 'maxPages' => $maxPages, 'currentPage' => $page]);
    }

    public function edit(Request $request, Aircraft $aircraft) {
        $gotStatus = $request->post('active');

        if($gotStatus == "on"){
            $finalStatus = true;
        } else {
            $finalStatus = false;
        }

        if($request->getMethod() == "POST"){
            $validated = $request->validate([
                'registration' => 'required|uppercase|max:6',
                'manufacturer' => 'required',
                'model' => 'required',
                'remarks' => 'nullable',
                'used_by' => 'required'
            ]);
            //TODO: When changing something, check if there is another aircraft on the same airline which is active.

            $targetAircraft = Aircraft::find($aircraft->id);
            $targetAircraft->registration = $request->post('registration');
            $targetAircraft->used_by = $request->post('used_by');
            $targetAircraft->manufacturer = $request->post('manufacturer');
            $targetAircraft->model = $request->post('model');
            $targetAircraft->active = $finalStatus;
            $targetAircraft->remarks = $request->post('remarks');

            if($targetAircraft->isDirty('registration') || $targetAircraft->isDirty('active')){
                if (Aircraft::where('active', 1)->where('registration', '=', $request->post('registration'))->where('used_by', '=', $request->post('used_by'))->count() >= 1) {
                    throw ValidationException::withMessages(['registration' => 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.']);
                } else {
                    $targetAircraft->save();
                }
            } else {
                $targetAircraft->save();
            }

            return redirect()->route('fleetmanager');
        }
        return view('fleet.edit', ['aircraft' => $aircraft ]);
    }
}
