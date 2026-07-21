<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'message', 'type', 'link', 'is_read'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // this table has no updated_at column

    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'message' => 'required',
    ];

    /** A user's notifications, newest first. */
    public function forUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /** A user's unread notifications, newest first. */
    public function unread(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function unreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', false)
            ->countAllResults();
    }

    public function markRead(int $id): bool
    {
        return (bool) $this->update($id, ['is_read' => true]);
    }

    /**
     * Mark every notification belonging to a user as read.
     * Uses the raw query builder (not Model::update()) since this is a
     * bulk WHERE-conditioned update rather than a single-row update by id.
     */
    public function markAllRead(int $userId): bool
    {
        return $this->builder()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Whether an unread notification with this exact type+link already
     * exists for a user — used to avoid inserting duplicate reminders.
     */
    public function existsUnread(int $userId, string $type, string $link): bool
    {
        return $this->where('user_id', $userId)
            ->where('type', $type)
            ->where('link', $link)
            ->where('is_read', false)
            ->countAllResults() > 0;
    }
}
