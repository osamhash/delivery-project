<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $count = Auth::user()->notifications()->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'تم تحديث الإشعار']);
    }

    public function markAllAsRead()
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'تم تحديث جميع الإشعارات']);
    }
}
