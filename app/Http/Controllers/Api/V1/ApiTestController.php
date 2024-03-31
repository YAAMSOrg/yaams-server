<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiTestController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth:sanctum');
    }

    public function index(){
        return response()->json([
            'message' => 'Just a test.'
        ]);
    }
}
