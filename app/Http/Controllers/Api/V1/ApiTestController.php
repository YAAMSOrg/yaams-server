<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class ApiTestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(){
        $username = request()->user('sanctum')->name;
        return response()->json([
            'message' => 'My name is ' . $username . '!'
        ]);
    }
}
