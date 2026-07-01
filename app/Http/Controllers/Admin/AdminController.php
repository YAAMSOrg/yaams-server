<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
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
            'users'           => User::count(),
            'unverified'      => User::whereNull('email_verified_at')->count(),
            'airlines'        => Airline::count(),
            'settings'        => Setting::count(),
        ];

        return view('admin.index', compact('stats'));
    }
}
