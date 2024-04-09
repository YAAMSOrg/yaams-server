<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aircraft;
use App\Models\Flight;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $currentActiveAirline = Session()->get('activeairline');
        $airlineFlights = $currentActiveAirline->flights;
        return view('dashboard.index', ['flights' => $airlineFlights]);
    }
}
