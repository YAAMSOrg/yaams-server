<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use Illuminate\Http\Request;

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

            Aircraft::create($validated);
        }

        $fleet = Aircraft::query()->orderBy('created_at', 'DESC')->get()->all();
        return view('fleet.index', ['fleet' => $fleet]);
    }
}