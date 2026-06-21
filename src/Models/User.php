<?php

namespace App\Models;

use App\Core\Database as DB;
use App\Helpers\ImageHelper;

class User {
    /**
     * Get the currently logged-in user ID.
     */
    public static function currentId(): int {
        return isset($_SESSION['userdata']['id']) ? (int)$_SESSION['userdata']['id'] : 0;
    }

    /**
     * Check if currently logged-in user is an administrator.
     */
    public static function isAdmin(): bool {
        return (int)($_SESSION['userdata']['is_admin'] ?? 0) === 1;
    }

    /**
     * Get user details by ID.
     */
    public static function getById(int $id): array {
        return DB::fetchOne("SELECT * FROM users WHERE id = ?", 'i', $id);
    }

    /**
     * Get user details by username.
     */
    public static function getByUsername(string $username): array {
        return DB::fetchOne("SELECT * FROM users WHERE username = ?", 's', $username);
    }

    /**
     * Get user details by email.
     */
    public static function getByEmail(string $email): array {
        return DB::fetchOne("SELECT * FROM users WHERE email = ?", 's', $email);
    }

    /**
     * Search users by keyword.
     */
    public static function search(string $keyword): array {
        $like = "%{$keyword}%";
        return DB::fetchAll(
            "SELECT * FROM users WHERE (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?) AND ac_status = 1 LIMIT 10",
            'sss', $like, $like, $like
        );
    }

    /**
     * Check if email is already registered.
     */
    public static function isEmailRegistered(string $email): bool {
        return DB::count("SELECT COUNT(*) as c FROM users WHERE email = ?", 's', $email) > 0;
    }

    /**
     * Check if username is already registered.
     */
    public static function isUsernameRegistered(string $username): bool {
        return DB::count("SELECT COUNT(*) as c FROM users WHERE username = ?", 's', $username) > 0;
    }

    /**
     * Check if username is registered by a different user.
     */
    public static function isUsernameRegisteredByOther(string $username): bool {
        $uid = self::currentId();
        return DB::count("SELECT COUNT(*) as c FROM users WHERE username = ? AND id != ?", 'si', $username, $uid) > 0;
    }

    /**
     * Get total number of registered users.
     */
    public static function getTotalCount(): int {
        return DB::count("SELECT COUNT(*) as c FROM users");
    }

    /**
     * Get all users for admin dashboard.
     */
    public static function getAllAdmin(int $offset = 0, int $limit = 20): array {
        return DB::fetchAll("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?", 'ii', $limit, $offset);
    }

    /**
     * Create a new user in the database.
     */
    public static function create(array $data): bool {
        return DB::execute(
            "INSERT INTO users (first_name, last_name, gender, email, username, password) VALUES (?,?,?,?,?,?)",
            'ssisss',
            trim($data['first_name']), trim($data['last_name']),
            (int)$data['gender'],
            strtolower(trim($data['email'])),
            trim($data['username']),
            password_hash(trim($data['password']), PASSWORD_BCRYPT)
        );
    }

    /**
     * Validate and check credentials.
     */
    public static function authenticate(array $creds): array {
        $usernameOrEmail = $creds['username_email'] ?? '';
        $user = self::getByUsername($usernameOrEmail)
            ?: DB::fetchOne("SELECT * FROM users WHERE email = ?", 's', $usernameOrEmail);

        if ($user) {
            $inputPassword = trim($creds['password'] ?? '');
            $verified = false;

            if (password_verify($inputPassword, $user['password'])) {
                $verified = true;
            } elseif (strlen($user['password']) === 32 && md5($inputPassword) === $user['password']) {
                // Legacy MD5 password match. Upgrade it to BCRYPT!
                $newHash = password_hash($inputPassword, PASSWORD_BCRYPT);
                DB::execute("UPDATE users SET password = ? WHERE id = ?", 'si', $newHash, (int)$user['id']);
                $user['password'] = $newHash;
                $verified = true;
            }

            if ($verified) {
                return ['status' => true, 'user' => $user];
            }
        }
        return ['status' => false, 'user' => []];
    }

    /**
     * Verify email address.
     */
    public static function verifyEmail(string $email): bool {
        return DB::execute("UPDATE users SET ac_status = 1 WHERE email = ?", 's', $email);
    }

    /**
     * Reset account password.
     */
    public static function resetPassword(string $email, string $password): bool {
        return DB::execute(
            "UPDATE users SET password = ? WHERE email = ?",
            'ss', password_hash(trim($password), PASSWORD_BCRYPT), $email
        );
    }

    /**
     * Update user profile details.
     */
    public static function updateProfile(array $data, array $img): bool {
        $uid = self::currentId();
        $current = self::getById($uid);

        $password = !empty(trim($data['password'] ?? ''))
            ? password_hash(trim($data['password']), PASSWORD_BCRYPT)
            : $current['password'];

        $pic = $current['profile_pic'];
        if (!empty($img['name'])) {
            $upload = ImageHelper::handleUpload($img, 'profile', 500);
            if (!$upload['status']) {
                $_SESSION['error'] = ['field' => 'profile_pic', 'msg' => $upload['error']];
                return false;
            }
            $pic = $upload['filename'];
        }

        $ok = DB::execute(
            "UPDATE users SET first_name=?, last_name=?, username=?, password=?, profile_pic=?, bio=?, website=? WHERE id=?",
            'sssssssi',
            trim($data['first_name']), trim($data['last_name']),
            trim($data['username']), $password, $pic,
            trim($data['bio'] ?? ''), trim($data['website'] ?? ''),
            $uid
        );

        if ($ok) {
            $_SESSION['userdata'] = self::getById($uid);
        }
        return $ok;
    }

    /**
     * Admin block/suspend user.
     */
    public static function block(int $uid): bool {
        return DB::execute("UPDATE users SET ac_status = 2 WHERE id = ?", 'i', $uid);
    }

    /**
     * Admin unsuspend user.
     */
    public static function unblock(int $uid): bool {
        return DB::execute("UPDATE users SET ac_status = 1 WHERE id = ?", 'i', $uid);
    }

    /**
     * Soft delete user account.
     */
    public static function softDelete(int $id): bool {
        return DB::execute(
            "UPDATE users SET first_name='Deleted', last_name='User', email=CONCAT('deleted_', id, '@samvadhub.com'), 
             username=CONCAT('deleted_user_', id), profile_pic='default_profile.jpg', ac_status=3 WHERE id=?",
            'i', $id
        );
    }
}
