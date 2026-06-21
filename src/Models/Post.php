<?php

namespace App\Models;

use App\Core\Database as DB;
use App\Helpers\ImageHelper;
use Exception;

class Post {
    /**
     * Get raw posts with author details.
     */
    public static function getPosts(int $offset = 0, int $limit = 20): array {
        return DB::fetchAll(
            "SELECT posts.*, users.id as uid, users.first_name, users.last_name,
                    users.username, users.profile_pic
             FROM posts JOIN users ON users.id = posts.user_id
             ORDER BY posts.id DESC LIMIT ? OFFSET ?",
            'ii', $limit, $offset
        );
    }

    /**
     * Filter posts based on follow relationships.
     */
    public static function filterPosts(int $offset = 0, int $limit = 20): array {
        $uid  = User::currentId();
        $list = self::getPosts($offset, $limit + 50);
        $out  = [];
        foreach ($list as $p) {
            if ($p['user_id'] === $uid || Social::checkFollowStatus((int)$p['user_id'])) {
                $out[] = $p;
                if (count($out) >= $limit) break;
            }
        }
        return $out;
    }

    /**
     * Get posts created by a specific user.
     */
    public static function getByUserId(int $userId): array {
        return DB::fetchAll(
            "SELECT * FROM posts WHERE user_id = ? ORDER BY id DESC", 'i', $userId
        );
    }

    /**
     * Get owner ID of a post.
     */
    public static function getPosterId(int $postId): ?int {
        $row = DB::fetchOne("SELECT user_id FROM posts WHERE id = ?", 'i', $postId);
        return $row ? (int)$row['user_id'] : null;
    }

    /**
     * Create a new post.
     */
    public static function create(array $text, ?array $image): bool {
        $uid  = User::currentId();
        $body = trim($text['post_text'] ?? '');
        $img  = '';

        if (!empty($image['name'])) {
            $upload = ImageHelper::handleUpload($image, 'posts', 1200);
            if (!$upload['status']) {
                $_SESSION['error'] = ['field' => 'post_img', 'msg' => $upload['error']];
                return false;
            }
            $img = $upload['filename'];
        }

        if (empty($body) && empty($img)) {
            $_SESSION['error'] = ['field' => 'post_img', 'msg' => 'Please add text or an image.'];
            return false;
        }

        return DB::execute(
            "INSERT INTO posts (user_id, post_text, post_img) VALUES (?,?,?)",
            'iss', $uid, $body, $img
        );
    }

    /**
     * Delete a post and its associated comments, likes, and notifications.
     */
    public static function delete(int $postId): bool {
        $uid = User::currentId();
        if (self::getPosterId($postId) !== $uid) {
            return false;
        }

        $conn = DB::getConnection();
        mysqli_begin_transaction($conn);
        try {
            DB::execute("DELETE FROM likes WHERE post_id = ?", 'i', $postId);
            DB::execute("DELETE FROM comments WHERE post_id = ?", 'i', $postId);
            DB::execute("UPDATE notifications SET read_status = 2 WHERE post_id = ? AND to_user_id = ?", 'ii', $postId, $uid);
            DB::execute("DELETE FROM posts WHERE id = ? AND user_id = ?", 'ii', $postId, $uid);
            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            return false;
        }
    }

    /**
     * Get explore posts sorted by like counts and blocks.
     */
    public static function getExplorePosts(int $offset = 0, int $limit = 20): array {
        $uid = User::currentId();
        return DB::fetchAll(
            "SELECT posts.*, users.id as uid, users.first_name, users.last_name,
                    users.username, users.profile_pic,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts JOIN users ON users.id = posts.user_id
             WHERE users.id != ?
               AND users.id NOT IN (SELECT blocked_user_id FROM block_list WHERE user_id = ?)
               AND users.id NOT IN (SELECT user_id FROM block_list WHERE blocked_user_id = ?)
               AND users.ac_status = 1
             ORDER BY like_count DESC, posts.id DESC LIMIT ? OFFSET ?",
            'iiiii', $uid, $uid, $uid, $limit, $offset
        );
    }

    /**
     * Search posts containing a specific keyword/hashtag, respecting user blocks.
     */
    public static function searchPosts(string $keyword, int $offset = 0, int $limit = 20): array {
        $uid = User::currentId();
        $likePattern = '%' . $keyword . '%';
        return DB::fetchAll(
            "SELECT posts.*, users.id as uid, users.first_name, users.last_name,
                    users.username, users.profile_pic,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts JOIN users ON users.id = posts.user_id
             WHERE (posts.post_text LIKE ?)
               AND users.id NOT IN (SELECT blocked_user_id FROM block_list WHERE user_id = ?)
               AND users.id NOT IN (SELECT user_id FROM block_list WHERE blocked_user_id = ?)
               AND users.ac_status = 1
             ORDER BY posts.id DESC LIMIT ? OFFSET ?",
            'siiii', $likePattern, $uid, $uid, $limit, $offset
        );
    }

    /**
     * Get total posts count.
     */
    public static function getTotalCount(): int {
        return DB::count("SELECT COUNT(*) as c FROM posts");
    }
}
