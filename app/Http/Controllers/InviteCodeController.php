<?php

namespace App\Http\Controllers;

use App\Models\InviteCode;
use Illuminate\Http\Request;

class InviteCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        $codes = InviteCode::where('airline_id', $airline->id)
            ->with(['creator', 'usedBy'])
            ->latest()
            ->get();

        return view('manager.invitecodes', compact('codes', 'airline'));
    }

    public function generate(Request $request)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        $request->validate(['role' => 'required|in:Pilot,Dispatcher,Manager']);

        do {
            $number = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $code   = strtoupper($airline->icao_callsign) . '-' . $number;
        } while (InviteCode::where('code', $code)->exists());

        InviteCode::create([
            'code'       => $code,
            'airline_id' => $airline->id,
            'created_by' => auth()->id(),
            'role'       => $request->role,
        ]);

        return back()->with('success', "Invite code created: {$code}");
    }

    public function destroy(InviteCode $inviteCode)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($inviteCode->airline_id === $airline->id, 403);
        abort_if($inviteCode->isUsed(), 422);

        $inviteCode->delete();

        return back()->with('success', 'Invite code deleted.');
    }
}
