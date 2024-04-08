<?php

namespace App\Http\Controllers;

use App\Models\AirlineMembership;
use Illuminate\Http\Request;    
use Illuminate\Validation\ValidationException;
use Session;

class AirlineMembershipController extends Controller
{
    public function changeActiveAirline(Request $request) {


        if($request->getMethod() == "POST"){
            // This is wrong! We get an AirlineMembership back, when we should really get a back an Airline.
            // Get data from the form.
            //$selectedTargetAirline = AirlineMembership::where('airline_id', '=', $request->post('airline_id'))->where('user_id', '=', auth()->user()->id)->first();

            $selectedTargetAirline = auth()->user()->airlines()
            ->where('airline_id', $request->post('airline_id'))
            ->first();

            $request->session()->forget('activeairline');
            Session::put('activeairline', $selectedTargetAirline);
            return redirect()->route('changeactiveairline');
        }

        $currentActiveAirline = $request->session()->get('activeairline');
        
        //TODO: Investige, if this can return an airline rather than AirlineMembership
        // We need this later to check if the user is not member of any airlines at all.
        $memberships = AirlineMembership::where('user_id', '=', auth()->user()->id)->get();
       
        return view('airlines.airlineswitcher', [ 'current_active' => $currentActiveAirline, 'memberships' => $memberships ]);
    }
}
