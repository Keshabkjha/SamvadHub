<?php

namespace App\Models;

use App\Core\Database as DB;

class Message {
    /**
     * Get IDs of users with active conversations.
     */
    public static function getActiveChatUserIds(): array {
        $uid = User::currentId();
        $data = DB::fetchAll(
            "SELECT from_user_id, to_user_id FROM messages WHERE to_user_id = ? OR from_user_id = ? ORDER BY id DESC",
            'ii', $uid, $uid
        );

        $ids = [];
        foreach ($data as $ch) {
            $from = (int)$ch['from_user_id'];
            $to = (int)$ch['to_user_id'];
            if ($from !== $uid && !in_array($from, $ids, true)) {
                $ids[] = $from;
            }
            if ($to !== $uid && !in_array($to, $ids, true)) {
                $ids[] = $to;
            }
        }
        return $ids;
    }

    /**
     * Get message history with a specific user.
     */
    public static function getMessages(int $userId): array {
        $uid = User::currentId();
        return DB::fetchAll(
            "SELECT * FROM messages 
             WHERE (to_user_id = ? AND from_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) 
             ORDER BY id DESC",
            'iiii', $uid, $userId, $userId, $uid
        );
    }

    /**
     * Send a message to a user.
     */
    public static function send(int $userId, string $msg): bool {
        $uid = User::currentId();
        $msg = trim($msg);
        if (empty($msg) || strlen($msg) > 2000) return false;

        return DB::execute(
            "INSERT INTO messages (from_user_id, to_user_id, msg) VALUES (?, ?, ?)",
            'iis', $uid, $userId, $msg
        );
    }

    /**
     * Get count of unread incoming messages.
     */
    public static function getUnreadCount(): int {
        $uid = User::currentId();
        return DB::count("SELECT COUNT(*) as c FROM messages WHERE to_user_id = ? AND read_status = 0", 'i', $uid);
    }

    /**
     * Mark messages from a specific user as read.
     */
    public static function markAsRead(int $userId): bool {
        $uid = User::currentId();
        return DB::execute(
            "UPDATE messages SET read_status = 1 WHERE to_user_id = ? AND from_user_id = ?",
            'ii', $uid, $userId
        );
    }

    /**
     * Get all active conversations with messages.
     */
    public static function getAllConversations(): array {
        $active_chat_ids = self::getActiveChatUserIds();
        $conversations   = [];
        foreach ($active_chat_ids as $index => $id) {
            $conversations[$index]['user_id']  = $id;
            $conversations[$index]['messages'] = self::getMessages($id);
        }
        return $conversations;
    }
}
