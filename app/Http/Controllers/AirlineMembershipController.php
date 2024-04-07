<?php

namespace App\Http\Controllers;

use App\Models\AirlineMembership;
use Illuminate\Http\Request;    

class AirlineMembershipController extends Controller
{

    public function changeActiveAirline(Request $request) {
        $currentActiveAirline = $request->session()->get('activeairlineid');
        $memberships = AirlineMembership::where('user_id', '=', auth()->user()->id)->get();

        if($request->getMethod() == "POST"){
            
        }

        return view('airlines.airlineswitcher', [ 'current_active' => $currentActiveAirline, 'memberships' => $memberships ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AirlineMembership $airlineMembership)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AirlineMembership $airlineMembership)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AirlineMembership $airlineMembership)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AirlineMembership $airlineMembership)
    {
        //
    }
}
