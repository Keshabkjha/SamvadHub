<?php
session_start();

// ─── Load .env file if present ───────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// ─── Database Configuration ───────────────────────────────────────────────────
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'SamvadHub');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');

// ─── CSRF Helper ─────────────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            die(json_encode(['status' => false, 'error' => 'Invalid CSRF token']));
        }
        die('Access Denied: Invalid CSRF token.');
    }
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

// ─── Output Helper ────────────────────────────────────────────────────────────
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function link_tags_mentions(?string $text): string {
    $text = e($text);
    // Link hashtags: #tag -> <a href="/explore?search=%23tag">#tag</a>
    $text = preg_replace_callback(
        '/#([a-zA-Z0-9_\x{00C0}-\x{00FF}]+)/u',
        function ($matches) {
            return '<a class="text-brand fw-600" style="text-decoration:none;" href="/explore?search=' . urlencode('#' . $matches[1]) . '">#' . e($matches[1]) . '</a>';
        },
        $text
    );
    // Link mentions: @username -> <a href="/profile/username">@username</a>
    $text = preg_replace_callback(
        '/@([a-zA-Z0-9_]+)/',
        function ($matches) {
            return '<a class="text-brand fw-600" style="text-decoration:none;" href="/profile/' . urlencode($matches[1]) . '">@' . e($matches[1]) . '</a>';
        },
        $text
    );
    return $text;
}

// ─── Auth Guard ───────────────────────────────────────────────────────────────
function requireAuth(): void {
    if (!isset($_SESSION['Auth'])) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(401);
            die(json_encode(['status' => false, 'error' => 'Unauthorized']));
        }
        header('Location: /login');
        exit;
    }
}

// ─── Form Helpers ─────────────────────────────────────────────────────────────
function showError(string $field): void {
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        if (isset($error['field']) && $field === $error['field']) {
            echo '<div class="alert alert-danger my-2" role="alert">' . e($error['msg']) . '</div>';
        }
    }
}

function showFormData(string $field): string {
    if (isset($_SESSION['formdata'])) {
        $formdata = $_SESSION['formdata'];
        return $formdata[$field] ?? '';
    }
    return '';
}

// ─── Time Helper ─────────────────────────────────────────────────────────────
function show_time(string $time): string {
    return '<time style="font-size:small" class="timeago text-muted text-small" datetime="' . htmlspecialchars($time, ENT_QUOTES) . '"></time>';
}

