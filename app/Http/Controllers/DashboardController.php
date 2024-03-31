<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $user = auth('sanctum')->user()->accessToken;

        //$current_auth_user_id = auth()->id();

        dump($user);
        return view('dashboard.index');
    }
}
