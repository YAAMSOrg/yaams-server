<?php

namespace App\Http\Controllers;

use App\Models\Notam;
use App\Notifications\NotamPosted;
use App\Support\Timezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        $notams = Notam::where('airline_id', $airline->id)
            ->with('author')
            ->latest()
            ->get();

        return view('manager.notams', compact('notams', 'airline'));
    }

    public function store(Request $request)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'expires_at' => 'nullable|date',
        ]);

        // The datetime-local field carries a naive wall-clock time in the
        // instance display timezone; store it as UTC.
        $expiresAt = Timezone::toUtc($validated['expires_at'] ?? null);

        if ($expiresAt && $expiresAt->isPast()) {
            return back()->withInput()->withErrors(['expires_at' => 'The expiry must be in the future.']);
        }

        $notam = Notam::create([
            'airline_id' => $airline->id,
            'created_by' => auth()->id(),
            'title'      => $validated['title'],
            'body'       => $validated['body'],
            'expires_at' => $expiresAt,
        ]);

        // Notify every airline member except the author (in-app + email).
        // Channels live in NotamPosted::via().
        $recipients = $airline->users()
            ->where('users.id', '!=', auth()->id())
            ->get();

        Notification::send($recipients, new NotamPosted($notam));

        return back()->with('success', 'NOTAM posted and crew notified.');
    }

    public function update(Request $request, Notam $notam)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($notam->airline_id === $airline->id, 403);

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'expires_at' => 'nullable|date',
        ]);

        $expiresAt = Timezone::toUtc($validated['expires_at'] ?? null);

        if ($expiresAt && $expiresAt->isPast()) {
            return back()->withInput()->withErrors(['expires_at' => 'The expiry must be in the future.']);
        }

        $notam->update([
            'title'      => $validated['title'],
            'body'       => $validated['body'],
            'expires_at' => $expiresAt,
        ]);

        return back()->with('success', 'NOTAM updated.');
    }

    public function destroy(Notam $notam)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($notam->airline_id === $airline->id, 403);

        $notam->delete();

        return back()->with('success', 'NOTAM deleted.');
    }
}
