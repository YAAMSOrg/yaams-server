<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Notifications\AircraftScreenshotSubmitted;
use App\Support\ActivityLevel;
use App\Support\AircraftImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
     * the bytes leave the server. Approved shots are visible to any member of the
     * owning airline; a pending shot is visible only to its uploader and to
     * Managers (who moderate it).
     */
    public function show(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('view', $aircraft)) {
            abort(403);
        }

        if ($image->isPending() && ! $this->canSeePending($request, $aircraft, $image)) {
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

    /**
     * Upload a screenshot. Any member of the owning airline may contribute.
     * A Manager's upload is auto-approved; anyone else's lands in the pending
     * queue and notifies the airline's Managers.
     */
    public function store(Request $request, Aircraft $aircraft)
    {
        if ($request->user()->cannot('uploadImage', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to upload screenshots for this aircraft.');
        }

        $request->validate(['screenshot' => AircraftImageProcessor::rules()]);

        // The cap counts every image (incl. pending) so the queue can't be flooded.
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

        $isModerator = $request->user()->can('update', $aircraft);

        $image = $aircraft->images()->create([
            'path' => $path,
            'uploaded_by' => $request->user()->id,
            'status' => $isModerator ? AircraftImage::STATUS_APPROVED : AircraftImage::STATUS_PENDING,
            'approved_at' => $isModerator ? now() : null,
            'approved_by' => $isModerator ? $request->user()->id : null,
            // The first approved shot becomes the primary (livery) image. A
            // pending upload is never primary until it is approved.
            'is_primary' => $isModerator && ! $this->hasPrimary($aircraft),
        ]);

        if ($isModerator) {
            activity()
                ->causedBy($request->user())
                ->performedOn($aircraft)
                ->withProperties(['level' => ActivityLevel::INFO])
                ->event('aircraft_image_uploaded')
                ->log('Uploaded a screenshot for aircraft '.$aircraft->registration);

            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('success', 'Screenshot uploaded.');
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($aircraft)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('aircraft_image_submitted')
            ->log('Submitted a screenshot for review on aircraft '.$aircraft->registration);

        Notification::send(
            $this->managers($aircraft, exceptUserId: $request->user()->id),
            new AircraftScreenshotSubmitted($image)
        );

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Screenshot submitted for review. A manager will approve it before it appears.');
    }

    /**
     * Approve a pending screenshot so it appears in the gallery. Managers only.
     */
    public function approve(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to manage this aircraft.');
        }

        if ($image->isApproved()) {
            return redirect()->route('viewaircraft', $aircraft->id);
        }

        $image->update([
            'status' => AircraftImage::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            // Become the livery shot if the aircraft doesn't have one yet.
            'is_primary' => ! $this->hasPrimary($aircraft),
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($aircraft)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('aircraft_image_approved')
            ->log('Approved a screenshot for aircraft '.$aircraft->registration);

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Screenshot approved.');
    }

    /**
     * Set the primary (livery) shot. Managers only, and only among approved
     * images.
     */
    public function setPrimary(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        if ($request->user()->cannot('update', $aircraft)) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to manage this aircraft.');
        }

        if (! $image->isApproved()) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'Only an approved screenshot can be the main image.');
        }

        DB::transaction(function () use ($aircraft, $image) {
            $aircraft->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return redirect()->route('viewaircraft', $aircraft->id)
            ->with('success', 'Primary screenshot updated.');
    }

    /**
     * Delete a screenshot. A Manager may delete any; a member may delete their
     * own upload (approved or still pending).
     */
    public function destroy(Request $request, Aircraft $aircraft, AircraftImage $image)
    {
        $isModerator = $request->user()->can('update', $aircraft);
        $isUploader = $image->uploaded_by === $request->user()->id;

        if (! $isModerator && ! $isUploader) {
            return redirect()->route('viewaircraft', $aircraft->id)
                ->with('error', 'You do not have permission to delete this screenshot.');
        }

        $wasPrimary = $image->is_primary;

        DB::transaction(function () use ($aircraft, $image, $wasPrimary) {
            $image->deleteFile();
            $image->delete();

            // Promote the most recent remaining approved image so the gallery
            // keeps a primary.
            if ($wasPrimary) {
                $next = $aircraft->approvedImages()->latest()->first();
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

    private function hasPrimary(Aircraft $aircraft): bool
    {
        return $aircraft->images()->where('is_primary', true)->exists();
    }

    /**
     * Whether the current user may see a pending image: its uploader, or a
     * Manager of the owning airline.
     */
    private function canSeePending(Request $request, Aircraft $aircraft, AircraftImage $image): bool
    {
        return $image->uploaded_by === $request->user()->id
            || $request->user()->can('update', $aircraft);
    }

    /**
     * The owning airline's Managers, optionally excluding one user.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function managers(Aircraft $aircraft, ?int $exceptUserId = null)
    {
        return $aircraft->airline->users()
            ->wherePivot('role', 'Manager')
            ->when($exceptUserId, fn ($q) => $q->where('users.id', '!=', $exceptUserId))
            ->get();
    }
}
