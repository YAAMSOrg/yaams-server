<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Setting;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Instance administration dashboard — overview of the whole instance.
     *
     * Access is restricted to Super-Admins via the `role:Super-Admin`
     * middleware on the route group (see routes/web.php).
     */
    public function index()
    {
        $stats = [
            'users'         => User::count(),
            'airlines'      => Airline::count(),
            'flights'       => Flight::count(),
            'aircraft'      => Aircraft::count(),
            'pendingPireps' => Flight::where('status_id', 1)->count(),
            'acceptedFlights' => Flight::where('status_id', 2)->count(),
        ];

        $recentUsers    = User::latest()->take(5)->get();
        $recentAirlines = Airline::latest()->take(5)->get();

        $instance = [
            'app_name'                    => Setting::get('app_name'),
            'allow_registration'          => Setting::get('allow_registration') === '1',
            'allow_user_airline_creation' => Setting::get('allow_user_airline_creation') === '1',
            'support_email'               => Setting::get('support_email'),
        ];

        return view('admin.index', compact('stats', 'recentUsers', 'recentAirlines', 'instance'));
    }
}
