<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::with('users')
            ->latest()
            ->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(SystemNotification $notification)
    {
        $notification->users()->updateExistingPivot(auth()->id(), [
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification marked as read.');
    }
}
