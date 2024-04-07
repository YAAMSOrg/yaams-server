<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AirlineController extends Controller
{
    public function changeActiveAirline(Request $request) {
        return view('airlines.airlineswitcher');
    }
}
