<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);
    }

    public function unread(Request $request)
    {
        return $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();
    }

    public function markAsRead(Request $request, $id)
    {
        $notif = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notif->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }
}
