<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Support\ActivityLevel;
use App\Support\AircraftImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AircraftImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Stream a screenshot from the private disk. This is the only path by which
     * the bytes leave the server - gated so only members of the owning airline
     * (via the active-airline session) can view it.
     */
    public function show(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('view', $aircraft)) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($image->path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $image->path,
            null,
            ['Cache-Control' => 'private, max-age=3600']
        );
    }

    public function store(Request $request, Aircraft $aircraft)
    {
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to manage this aircraft.');
        }

        $request->validate(['screenshot' => AircraftImageProcessor::rules()]);

        if ($aircraft->images()->count() >= AircraftImageProcessor::maxPerAircraft()) {
            throw ValidationException::withMessages([
                'screenshot' => 'This aircraft already has the maximum number of screenshots. Delete one first.',
            ]);
        }

        try {
            $path = AircraftImageProcessor::store($request->file('screenshot'), $aircraft->id);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['screenshot' => $e->getMessage()]);
        }

        $aircraft->images()->create([
            'path' => $path,
            // The first screenshot uploaded becomes the primary (livery) shot.
            'is_primary' => ! $aircraft->images()->exists(),
            'uploaded_by' => $request->user()->id,
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($aircraft)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('aircraft_image_uploaded')
            ->log('Uploaded a screenshot for aircraft '.$aircraft->registration);

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Screenshot uploaded.');
    }

    public function setPrimary(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to manage this aircraft.');
        }

        DB::transaction(function () use ($aircraft, $image) {
            $aircraft->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Primary screenshot updated.');
    }

    public function destroy(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to manage this aircraft.');
        }

        $wasPrimary = $image->is_primary;

        DB::transaction(function () use ($aircraft, $image, $wasPrimary) {
            $image->deleteFile();
            $image->delete();

            // Promote the most recent remaining image so the gallery keeps a primary.
            if ($wasPrimary) {
                $next = $aircraft->images()->latest()->first();
                $next?->update(['is_primary' => true]);
            }
        });

        activity()
            ->causedBy($request->user())
            ->performedOn($aircraft)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('aircraft_image_deleted')
            ->log('Deleted a screenshot for aircraft '.$aircraft->registration);

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Screenshot deleted.');
    }
}
