<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function getUser()
    {
        return Auth::guard('admin')->user()
            ?? Auth::guard('employee')->user();
    }

    public function index()
    {
        $user          = $this->getUser();
        $notifications = $user->notifications()->paginate(20);
        $user->unreadNotifications->markAsRead();

        $guard = Auth::guard('admin')->check() ? 'admin' : 'employee';
        return view('notifications.index', compact('notifications', 'guard'));
    }

    public function markAllRead()
    {
        $this->getUser()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function markRead(string $id)
    {
        $user         = $this->getUser();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect($notification->data['url'] ?? back()->getTargetUrl());
    }
}