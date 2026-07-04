<?php

namespace App\Http\Controllers;

use App\Models\InviteCode;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('portal.index', [
            'airlines' => auth()->user()->airlines,
        ]);
    }

    public function redeem(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);

        $code = InviteCode::where('code', strtoupper(trim($request->code)))
            ->whereNull('used_by')
            ->first();

        if (!$code) {
            return back()->withErrors(['code' => 'This invite code is invalid or has already been used.']);
        }

        if (auth()->user()->airlines()->where('airline_id', $code->airline_id)->exists()) {
            return back()->withErrors(['code' => 'You are already a member of this airline.']);
        }

        auth()->user()->airlines()->attach($code->airline_id, ['role' => $code->role]);

        $code->update([
            'used_by' => auth()->id(),
            'used_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($code->airline)
            ->withProperties(['level' => ActivityLevel::INFO, 'role' => $code->role])
            ->event('invite_redeemed')
            ->log('Redeemed invite code to join ' . $code->airline->name);

        if (!session('activeairline')) {
            $request->session()->put('activeairline', $code->airline);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to ' . $code->airline->name . '! You joined as ' . $code->role . '.');
    }
}
