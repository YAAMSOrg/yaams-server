<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AirlineMembership;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(){
        return view('auth.login');
    }

    public function store(Request $request){
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(!auth()->attempt($request->only('email', 'password'), $request->remember)) {
             return back()->with('status', 'Invalid login details');
        }

        // Set the first airline found for the user in the DB as the active airline.
        // FIXME/TODO: What to do if no airline exists??
        $firstAirlineFound = AirlineMembership::where('user_id', '=', auth()->user()->id)->first();
        $request->session()->put('activeairline', $firstAirlineFound);

        return redirect()->route('dashboard');
    }
}
