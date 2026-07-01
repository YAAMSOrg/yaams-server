<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
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
            'users'    => User::count(),
            'airlines' => Airline::count(),
            'flights'  => Flight::count(),
            'aircraft' => Aircraft::count(),
        ];

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.index', compact('stats', 'recentUsers'));
    }
}
