<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users');
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    public function notifications()
    {
        return view('admin.notifications');
    }

    public function getNotification(Request $request)
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications->count();

        if ($request->has('all')) {
            $notifications = $user->notifications;
        }
        else {
            // In modal show latest 10
            $notifications = $user->notifications()->latest()->limit(10)->get();
        }

        return response()->json([
            'status' => true,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $id = null)
    {
        $user = auth()->user();
        if ($id) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read'
        ]);
    }
}
