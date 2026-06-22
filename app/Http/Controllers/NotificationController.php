<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    private function getUser()
    {
        return Auth::guard('admin')->user()
            ?? Auth::guard('employee')->user();
    }

    public function index()
    {
        $user          = $this->getUser();
        $notifications = $this->notificationService->getForUser($user);
        $guard         = Auth::guard('admin')->check() ? 'admin' : 'employee';

        return view('notifications.index', compact('notifications', 'guard'));
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead($this->getUser());
        return back()->with('success', 'All notifications marked as read.');
    }

    public function markRead(string $id)
    {
        $url = $this->notificationService->markReadAndGetUrl($this->getUser(), $id);
        return redirect($url);
    }
}