<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user('api')
            ->notifications()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data' => NotificationResource::collection($notifications),
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user('api')
            ->unreadNotifications()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Unread notifications retrieved successfully.',
            'data' => NotificationResource::collection($notifications),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user('api')
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read successfully.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user('api')
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read successfully.',
        ]);
    }
}