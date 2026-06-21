<?php

namespace App\Helpers;

use App\Models\User;

class ValidationHelper {
    /**
     * Validate user signup data.
     */
    public static function validateSignup(array $data): array {
        if (empty(trim($data['first_name'] ?? ''))) {
            return ['status' => false, 'msg' => 'First name is required.', 'field' => 'first_name'];
        }
        if (empty(trim($data['last_name'] ?? ''))) {
            return ['status' => false, 'msg' => 'Last name is required.', 'field' => 'last_name'];
        }
        if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => false, 'msg' => 'Please enter a valid email address.', 'field' => 'email'];
        }
        if (empty(trim($data['username'] ?? ''))) {
            return ['status' => false, 'msg' => 'Username is required.', 'field' => 'username'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['username'])) {
            return ['status' => false, 'msg' => 'Username must be 3–30 characters (letters, numbers, underscore only).', 'field' => 'username'];
        }
        if (strlen(trim($data['password'] ?? '')) < 6) {
            return ['status' => false, 'msg' => 'Password must be at least 6 characters.', 'field' => 'password'];
        }
        if (User::isEmailRegistered(strtolower(trim($data['email'])))) {
            return ['status' => false, 'msg' => 'This email is already registered.', 'field' => 'email'];
        }
        if (User::isUsernameRegistered(trim($data['username']))) {
            return ['status' => false, 'msg' => 'This username is already taken.', 'field' => 'username'];
        }
        return ['status' => true];
    }

    /**
     * Validate user login credentials.
     */
    public static function validateLogin(array $data): array {
        if (empty(trim($data['username_email'] ?? ''))) {
            return ['status' => false, 'msg' => 'Please enter your username or email.', 'field' => 'username_email'];
        }
        if (empty(trim($data['password'] ?? ''))) {
            return ['status' => false, 'msg' => 'Please enter your password.', 'field' => 'password'];
        }
        $result = User::authenticate($data);
        if (!$result['status']) {
            return ['status' => false, 'msg' => 'Invalid credentials. Please try again.', 'field' => 'checkuser'];
        }
        return ['status' => true, 'user' => $result['user']];
    }

    /**
     * Validate profile update fields.
     */
    public static function validateUpdate(array $data): array {
        if (empty(trim($data['first_name'] ?? ''))) {
            return ['status' => false, 'msg' => 'First name is required.', 'field' => 'first_name'];
        }
        if (empty(trim($data['last_name'] ?? ''))) {
            return ['status' => false, 'msg' => 'Last name is required.', 'field' => 'last_name'];
        }
        if (empty(trim($data['username'] ?? ''))) {
            return ['status' => false, 'msg' => 'Username is required.', 'field' => 'username'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['username'])) {
            return ['status' => false, 'msg' => 'Username must be 3–30 characters (letters, numbers, underscore only).', 'field' => 'username'];
        }
        if (User::isUsernameRegisteredByOther(trim($data['username']))) {
            return ['status' => false, 'msg' => htmlspecialchars($data['username'], ENT_QUOTES) . ' is already taken.', 'field' => 'username'];
        }
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            return ['status' => false, 'msg' => 'Please enter a valid website URL.', 'field' => 'website'];
        }
        return ['status' => true];
    }
}
