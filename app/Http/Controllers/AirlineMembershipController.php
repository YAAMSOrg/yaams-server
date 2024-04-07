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
            // Get data from the form.
            $selectedTargetAirline = AirlineMembership::where('airline_id', '=', $request->post('airline_id'))->where('user_id', '=', auth()->user()->id)->first();

            //dd($selectedTargetAirline);

            // Double check if the user is member
            if ($selectedTargetAirline->count() == 0) {
                throw ValidationException::withMessages(['airline_id' => 'You are not a member of this airline.']);
            } else {
                
                $request->session()->forget('activeairline');
                Session::put('activeairline', $selectedTargetAirline);
                $currentActiveAirline = $request->session()->get('activeairline');
                //dump($currentActiveAirline->airline->name);
                return redirect()->route('changeactiveairline');
            }
        }

        $currentActiveAirline = $request->session()->get('activeairline');

        // We need this later to check if the user is not member of any airlines at all.
        $memberships = AirlineMembership::where('user_id', '=', auth()->user()->id)->get();
        return view('airlines.airlineswitcher', [ 'current_active' => $currentActiveAirline, 'memberships' => $memberships ]);
    }
}
