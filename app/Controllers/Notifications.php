<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    protected NotificationModel $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationModel();
    }

    /** The logged-in user's notifications, newest first. */
    public function index()
    {
        $userId = (int) session()->get('user_id');

        return view('notifications/index', [
            'title'         => 'Notifications',
            'active'        => 'notifications',
            'notifications' => $userId ? $this->notifications->forUser($userId) : [],
        ]);
    }

    /** POST — mark a single notification read, then return to wherever the click came from. */
    public function markRead($id)
    {
        $userId       = (int) session()->get('user_id');
        $notification = $this->notifications->find((int) $id);

        // Only the owning user may mark their own notification read.
        if ($notification && (int) $notification['user_id'] === $userId) {
            $this->notifications->markRead((int) $id);
        }

        return redirect()->back();
    }

    /** GET — small JSON endpoint for a header bell-icon unread badge. */
    public function unreadCount()
    {
        $userId = (int) session()->get('user_id');

        return $this->response->setJSON([
            'count' => $userId ? $this->notifications->unreadCount($userId) : 0,
        ]);
    }
}
