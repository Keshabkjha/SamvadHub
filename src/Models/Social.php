<?php

namespace App\Models;

use App\Core\Database as DB;

class Social {
    /**
     * Check if current user is following target user.
     */
    public static function checkFollowStatus(int $userId): int {
        return DB::count(
            "SELECT COUNT(*) as c FROM follow_list WHERE follower_id = ? AND user_id = ?",
            'ii', User::currentId(), $userId
        );
    }

    /**
     * Follow a user.
     */
    public static function follow(int $userId): bool {
        $uid = User::currentId();
        if ($uid === $userId || self::checkFollowStatus($userId)) {
            return false;
        }

        $ok = DB::execute("INSERT INTO follow_list (follower_id, user_id) VALUES (?,?)", 'ii', $uid, $userId);
        if ($ok) {
            Notification::create($uid, $userId, "started following you!");
        }
        return $ok;
    }

    /**
     * Unfollow a user.
     */
    public static function unfollow(int $userId): bool {
        $uid = User::currentId();
        $ok  = DB::execute("DELETE FROM follow_list WHERE follower_id = ? AND user_id = ?", 'ii', $uid, $userId);
        if ($ok) {
            Notification::create($uid, $userId, "unfollowed you.");
        }
        return $ok;
    }

    /**
     * Get followers list.
     */
    public static function getFollowers(int $userId): array {
        return DB::fetchAll("SELECT * FROM follow_list WHERE user_id = ?", 'i', $userId);
    }

    /**
     * Get list of users being followed.
     */
    public static function getFollowing(int $userId): array {
        return DB::fetchAll("SELECT * FROM follow_list WHERE follower_id = ?", 'i', $userId);
    }

    /**
     * Filter and suggest people to follow.
     */
    public static function filterFollowSuggestions(): array {
        $uid  = User::currentId();
        $list = DB::fetchAll("SELECT * FROM users WHERE id != ? AND ac_status = 1 LIMIT 20", 'i', $uid);
        $out  = [];
        foreach ($list as $u) {
            if (!self::checkFollowStatus($u['id']) && !self::checkBS($u['id']) && count($out) < 5) {
                $out[] = $u;
            }
        }
        return $out;
    }

    /**
     * Check if a block exists between blocker and blocked.
     */
    public static function checkBlockStatus(int $blocker, int $blocked): int {
        return DB::count(
            "SELECT COUNT(*) as c FROM block_list WHERE user_id = ? AND blocked_user_id = ?",
            'ii', $blocker, $blocked
        );
    }

    /**
     * Check block status bidirectionally (block status screen).
     */
    public static function checkBS(int $userId): int {
        $uid = User::currentId();
        return DB::count(
            "SELECT COUNT(*) as c FROM block_list
             WHERE (user_id=? AND blocked_user_id=?) OR (user_id=? AND blocked_user_id=?)",
            'iiii', $uid, $userId, $userId, $uid
        );
    }

    /**
     * Block a user (and remove mutual follow relationships).
     */
    public static function block(int $userId): bool {
        $uid = User::currentId();
        DB::execute(
            "DELETE FROM follow_list WHERE (follower_id=? AND user_id=?) OR (follower_id=? AND user_id=?)",
            'iiii', $uid, $userId, $userId, $uid
        );
        $ok = DB::execute("INSERT INTO block_list (user_id, blocked_user_id) VALUES (?,?)", 'ii', $uid, $userId);
        if ($ok) {
            Notification::create($uid, $userId, "blocked you.");
        }
        return $ok;
    }

    /**
     * Unblock a user.
     */
    public static function unblock(int $userId): bool {
        $uid = User::currentId();
        $ok  = DB::execute("DELETE FROM block_list WHERE user_id=? AND blocked_user_id=?", 'ii', $uid, $userId);
        if ($ok) {
            Notification::create($uid, $userId, "unblocked you.");
        }
        return $ok;
    }

    /**
     * Check if a post is liked by the current user.
     */
    public static function checkLikeStatus(int $postId): int {
        return DB::count(
            "SELECT COUNT(*) as c FROM likes WHERE user_id=? AND post_id=?",
            'ii', User::currentId(), $postId
        );
    }

    /**
     * Get likes list for a post.
     */
    public static function getLikes(int $postId): array {
        return DB::fetchAll("SELECT * FROM likes WHERE post_id=?", 'i', $postId);
    }

    /**
     * Like a post.
     */
    public static function like(int $postId): bool {
        if (self::checkLikeStatus($postId)) return false;
        $uid = User::currentId();
        $ok  = DB::execute("INSERT INTO likes (post_id, user_id) VALUES (?,?)", 'ii', $postId, $uid);
        if ($ok && ($pid = Post::getPosterId($postId)) && $pid !== $uid) {
            Notification::create($uid, $pid, "liked your post!", $postId);
        }
        return $ok;
    }

    /**
     * Unlike a post.
     */
    public static function unlike(int $postId): bool {
        if (!self::checkLikeStatus($postId)) return false;
        $uid = User::currentId();
        $ok  = DB::execute("DELETE FROM likes WHERE user_id=? AND post_id=?", 'ii', $uid, $postId);
        if ($ok && ($pid = Post::getPosterId($postId)) && $pid !== $uid) {
            Notification::create($uid, $pid, "unliked your post.", $postId);
        }
        return $ok;
    }

    /**
     * Get comments for a post.
     */
    public static function getComments(int $postId): array {
        return DB::fetchAll("SELECT * FROM comments WHERE post_id=? ORDER BY id DESC", 'i', $postId);
    }

    /**
     * Add a comment to a post.
     */
    public static function addComment(int $postId, string $comment): bool {
        $comment = trim($comment);
        if (empty($comment) || strlen($comment) > 1000) return false;
        $uid = User::currentId();
        $ok  = DB::execute("INSERT INTO comments (user_id, post_id, comment) VALUES (?,?,?)", 'iis', $uid, $postId, $comment);
        if ($ok && ($pid = Post::getPosterId($postId)) && $pid !== $uid) {
            Notification::create($uid, $pid, "commented on your post.", $postId);
        }
        return $ok;
    }

    /**
     * Delete a comment.
     */
    public static function deleteComment(int $commentId): bool {
        return DB::execute("DELETE FROM comments WHERE id=? AND user_id=?", 'ii', $commentId, User::currentId());
    }

    /**
     * Check if a post is bookmarked.
     */
    public static function isPostBookmarked(int $postId): bool {
        return DB::count("SELECT COUNT(*) as c FROM bookmarks WHERE user_id=? AND post_id=?", 'ii', User::currentId(), $postId) > 0;
    }

    /**
     * Bookmark a post.
     */
    public static function bookmarkPost(int $postId): bool {
        if (self::isPostBookmarked($postId)) return false;
        return DB::execute("INSERT INTO bookmarks (user_id, post_id) VALUES (?,?)", 'ii', User::currentId(), $postId);
    }

    /**
     * Unbookmark a post.
     */
    public static function unbookmarkPost(int $postId): bool {
        return DB::execute("DELETE FROM bookmarks WHERE user_id=? AND post_id=?", 'ii', User::currentId(), $postId);
    }

    /**
     * Get all bookmarked posts for current user.
     */
    public static function getBookmarkedPosts(): array {
        $uid = User::currentId();
        return DB::fetchAll(
            "SELECT posts.*, users.first_name, users.last_name, users.username,
                    users.profile_pic, users.id as uid
             FROM bookmarks
             JOIN posts ON posts.id = bookmarks.post_id
             JOIN users ON users.id = posts.user_id
             WHERE bookmarks.user_id = ? ORDER BY bookmarks.id DESC",
            'i', $uid
        );
    }

    /**
     * Report/Flag a user.
     */
    public static function reportUser(int $reportedId, string $reason): bool {
        return DB::execute(
            "INSERT INTO reports (reporter_id, reported_user_id, reason) VALUES (?,?,?)",
            'iis', User::currentId(), $reportedId, $reason
        );
    }

    /**
     * Report/Flag a post.
     */
    public static function reportPost(int $postId, string $reason): bool {
        return DB::execute(
            "INSERT INTO reports (reporter_id, post_id, reason) VALUES (?,?,?)",
            'iis', User::currentId(), $postId, $reason
        );
    }

    /**
     * Get pending moderation reports.
     */
    public static function getPendingReports(): array {
        return DB::fetchAll("SELECT * FROM reports WHERE status = 0 ORDER BY id DESC LIMIT 50");
    }

    /**
     * Dismiss a moderation report.
     */
    public static function dismissReport(int $rid): bool {
        return DB::execute("UPDATE reports SET status = 2 WHERE id = ?", 'i', $rid);
    }
}
