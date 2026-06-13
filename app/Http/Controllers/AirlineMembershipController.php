<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AirlineMembershipController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function changeActiveAirline(Request $request) 
    {
        $request->validate([
            'airline_id' => 'required|exists:airlines,id'
        ]);

        // Sicherstellen, dass der User auch wirklich Mitglied in dieser Airline ist
        $selectedAirline = $request->user()->airlines()
            ->where('airline_id', $request->input('airline_id'))
            ->first();

        if (!$selectedAirline) {
            return back()->withErrors(['airline' => 'You are not a member of this airline.']);
        }

        // Echtes Airline-Modell statt Membership in die Session legen
        Session::put('activeairline', $selectedAirline);

        // Den User wieder dorthin zurückschicken, wo er herkam
        return back()->with('success', "Switched to airline: {$selectedAirline->name}");
    }
}