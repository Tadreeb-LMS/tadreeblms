<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Get unread notifications for the logged in user
     */
    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();

        $notifications = UserNotification::forUser($user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $unreadCount = UserNotification::forUser($user->id)
            ->unread()
            ->count();

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'icon_color' => $notification->icon_color,
                    'link' => $notification->link,
                    'time' => $notification->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = UserNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->markAsRead();

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back();
        }

        if ($request->ajax()) {
            return response()->json(['success' => false], 404);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        UserNotification::forUser(Auth::id())
            ->unread()
            ->update(['is_read' => true]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Get all notifications (for viewing all notifications page)
     */
    public function index()
    {
        $notifications = UserNotification::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.notifications.index', compact('notifications'));
    }
}
