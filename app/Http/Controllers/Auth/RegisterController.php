<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(){
        return view('auth.register');
    }

    public function store(Request $request){
        // Validate the users register request
        $this->validate($request, [
            'name' => 'required|max:255|string',
            'email' => 'required|email|max:255',
            'homebase' => 'max:4',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'homebase' => Str::upper($request->homebase),
            'password' => Hash::make($request->password)
        ]);

        //Default role of every registered user is pilot
        $user->assignRole('Pilot');

        //Sign in user
        auth()->attempt($request->only('email', 'password'));

        return redirect()->route('dashboard');
    }
}
