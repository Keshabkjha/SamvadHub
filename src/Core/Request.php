<?php

namespace App\Core;

class Request {
    private array $routeParams = [];

    /**
     * Get request method (GET, POST, etc.)
     */
    public function getMethod(): string {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        return $method === 'HEAD' ? 'GET' : $method;
    }

    /**
     * Get request URI path, normalized (e.g. /profile/john)
     */
    public function getPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        return '/' . trim($uri, '/');
    }

    /**
     * Fetch GET parameter
     */
    public function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Fetch POST parameter
     */
    public function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Fetch input from either GET or POST
     */
    public function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Fetch all inputs combined
     */
    public function all(): array {
        return array_merge($_GET, $_POST);
    }

    /**
     * Fetch file parameter
     */
    public function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    /**
     * Manage Route Parameters (e.g., from /profile/{username})
     */
    public function setRouteParams(array $params): void {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, $default = null) {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * CSRF verification helper
     */
    public function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!isset($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            if ($this->isAjax()) {
                http_response_code(403);
                echo json_encode(['status' => false, 'error' => 'Invalid CSRF token']);
                exit;
            }
            http_response_code(403);
            die('Access Denied: Invalid CSRF token.');
        }
        // Regenerate after verification to prevent reuse
        return true;
    }

    /**
     * Rate Limiting Helper
     */
    public function checkRateLimit(string $key, int $max_attempts, int $window_seconds): bool {
        $session_key = 'rate_' . $key;
        $now = time();
        if (!isset($_SESSION[$session_key])) {
            $_SESSION[$session_key] = ['count' => 0, 'reset_at' => $now + $window_seconds];
        }
        if ($now > $_SESSION[$session_key]['reset_at']) {
            $_SESSION[$session_key] = ['count' => 0, 'reset_at' => $now + $window_seconds];
        }
        $_SESSION[$session_key]['count']++;
        return $_SESSION[$session_key]['count'] <= $max_attempts;
    }
}
