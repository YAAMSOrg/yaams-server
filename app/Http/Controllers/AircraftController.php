<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use Illuminate\Http\Request;

class AircraftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $fleet = Aircraft::query()->orderBy('created_at', 'DESC')->get()->all();
        return view('fleet.index', ['fleet' => $fleet]);
    }

    public function store(){
        return view('fleet.index');
    }

}
