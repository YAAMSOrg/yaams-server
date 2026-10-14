<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListNotificationsRequest;
use App\Http\Resources\V1\NotificationCollection;
use App\Http\Resources\V1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Notifications
 *
 * The token owner's in-app notifications (the bell in the web UI). Every
 * endpoint only sees the caller's own notifications - another user's
 * notification id is a `404`.
 *
 * `type` is a stable key of the event, one of `pirepFiled`, `pirepAccepted`,
 * `pirepRejected`, `aircraftScreenshotSubmitted`, `notamPosted`,
 * `inviteCodeRedeemed`, `addedToAirline`, `removedFromAirline`,
 * `twoFactorReset`. New types may be added without a version bump, so treat
 * an unknown type as a generic notification. `url` points to the related web
 * page and may be `null`.
 */
class NotificationAPIController extends Controller
{
    /**
     * List notifications
     *
     * The caller's notifications, newest first by default.
     *
     * @queryParam unread boolean Only unread (`true`) or only read (`false`) notifications. Example: true
     * @queryParam sort string One of `created_at`, `read_at`, prefixed with `-` for descending. Defaults to `-created_at`. Example: -created_at
     * @queryParam page integer Page number, from 1. Example: 1
     * @queryParam per_page integer Items per page, 1 - 100. Defaults to 25. Example: 25
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "9d3c6f2e-4b1a-4c8e-9f0a-2b7d5e8c1a34",
     *       "type": "pirepAccepted",
     *       "title": "PIREP accepted",
     *       "message": "Your flight EV123 from EDDF to EGLL was accepted.",
     *       "url": "https://yaams.example.com/flights/42",
     *       "readAt": null,
     *       "createdAt": "2026-01-01T12:00:00.000000Z"
     *     }
     *   ],
     *   "links": {
     *     "first": "https://yaams.example.com/api/v1/notifications?page=1",
     *     "last": "https://yaams.example.com/api/v1/notifications?page=1",
     *     "prev": null,
     *     "next": null
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 1,
     *     "path": "https://yaams.example.com/api/v1/notifications",
     *     "per_page": 25,
     *     "to": 1,
     *     "total": 1
     *   }
     * }
     */
    public function index(ListNotificationsRequest $request): NotificationCollection
    {
        // reorder(): the notifications() relation has a built-in latest()
        // that would otherwise take precedence over the requested sort.
        return new NotificationCollection($request->paginate($request->user()->notifications()->reorder()));
    }

    /**
     * Unread count
     *
     * Number of unread notifications - a cheap request for polling a
     * notification bell.
     *
     * @response 200 {"count": 3}
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    /**
     * Mark as read
     *
     * Acknowledges one notification and returns it. Marking an already read
     * notification again keeps its original `readAt`.
     *
     * @urlParam notification string required The notification id (UUID). Example: 9d3c6f2e-4b1a-4c8e-9f0a-2b7d5e8c1a34
     *
     * @response 200 {
     *   "data": {
     *     "id": "9d3c6f2e-4b1a-4c8e-9f0a-2b7d5e8c1a34",
     *     "type": "pirepAccepted",
     *     "title": "PIREP accepted",
     *     "message": "Your flight EV123 from EDDF to EGLL was accepted.",
     *     "url": "https://yaams.example.com/flights/42",
     *     "readAt": "2026-01-01T12:05:00.000000Z",
     *     "createdAt": "2026-01-01T12:00:00.000000Z"
     *   }
     * }
     * @response 404 {"message": "No query results for model [Illuminate\\Notifications\\DatabaseNotification] 9d3c6f2e-4b1a-4c8e-9f0a-2b7d5e8c1a34"}
     */
    public function read(Request $request, string $notification): NotificationResource
    {
        // Resolved through the user's own relation, so another user's
        // notification is a 404 and its existence is not revealed.
        $notification = $request->user()->notifications()->findOrFail($notification);

        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    /**
     * Mark all as read
     *
     * Acknowledges every unread notification of the caller.
     *
     * @response 204 scenario="All notifications marked as read"
     */
    public function readAll(Request $request): Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
