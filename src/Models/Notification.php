<?php

namespace App\Models;

use App\Core\Database as DB;

class Notification {
    /**
     * Create a notification.
     */
    public static function create(int $fromUserId, int $toUserId, string $msg, int $postId = 0): void {
        if ($fromUserId === $toUserId) return; // Don't notify self
        DB::execute(
            "INSERT INTO notifications (from_user_id, to_user_id, message, post_id) VALUES (?, ?, ?, ?)",
            'iiis', $fromUserId, $toUserId, $msg, $postId
        );
    }

    /**
     * Get notifications for the current user.
     */
    public static function getForCurrentUser(): array {
        $uid = User::currentId();
        return DB::fetchAll(
            "SELECT * FROM notifications WHERE to_user_id = ? ORDER BY id DESC LIMIT 50",
            'i', $uid
        );
    }

    /**
     * Get count of unread notifications for current user.
     */
    public static function getUnreadCount(): int {
        $uid = User::currentId();
        return DB::count(
            "SELECT COUNT(*) as c FROM notifications WHERE to_user_id = ? AND read_status = 0",
            'i', $uid
        );
    }

    /**
     * Mark all notifications for current user as read.
     */
    public static function markAllAsRead(): bool {
        $uid = User::currentId();
        return DB::execute(
            "UPDATE notifications SET read_status = 1 WHERE to_user_id = ?",
            'i', $uid
        );
    }
}
