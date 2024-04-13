<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AirlineController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

}
