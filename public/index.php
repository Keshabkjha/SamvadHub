<?php
// public/index.php

// ─── Autoloading & Config ───────────────────────────────────────────
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    spl_autoload_register(function ($class) {
        // App\ namespace mapping
        if (str_starts_with($class, 'App\\')) {
            $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
        // PHPMailer namespace mapping
        if (str_starts_with($class, 'PHPMailer\\PHPMailer\\')) {
            $path = dirname(__DIR__) . '/src/PHPMailer/src/' . str_replace('\\', '/', substr($class, 20)) . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
            $path = dirname(__DIR__) . '/assets/php/PHPMailer/src/' . str_replace('\\', '/', substr($class, 20)) . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });
}
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Response;
use App\Models\User;

// ─── Centralized Account Status Checks ──────────────────────────────
if (isset($_SESSION['Auth'])) {
    $user = $_SESSION['userdata'] ?? null;
    if ($user) {
        // Fetch fresh copy to ensure status is up to date
        $freshUser = User::getById((int)$user['id']);
        if ($freshUser) {
            $_SESSION['userdata'] = $freshUser;
            $user = $freshUser;
        }

        // Account Soft-Deleted
        if ((int)$user['ac_status'] === 3) {
            session_destroy();
            Response::redirect('/login?deleted');
        }

        // Account Suspended
        if ((int)$user['ac_status'] === 2) {
            $path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            if ($path !== 'logout') {
                Response::renderView('blocked', ['page_title' => 'Account Suspended']);
            }
        }

        // Account Email Unverified
        if ((int)$user['ac_status'] === 0) {
            $path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $allowed = ['verify-email', 'logout', 'resend-verification'];
            if (!in_array($path, $allowed, true)) {
                Response::renderView('verify_email', ['page_title' => 'Verify Email']);
            }
        }
    }
}

// ─── Route Declarations ──────────────────────────────────────────────
$router  = new Router();
$request = new Request();

// Auth Routes
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/signup', [\App\Controllers\AuthController::class, 'showSignup']);
$router->post('/signup', [\App\Controllers\AuthController::class, 'signup']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

$router->get('/forgot-password', [\App\Controllers\AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);
$router->post('/verify-forgot-code', [\App\Controllers\AuthController::class, 'verifyForgotCode']);
$router->post('/reset-password', [\App\Controllers\AuthController::class, 'resetPassword']);

$router->post('/verify-email', [\App\Controllers\AuthController::class, 'verifyEmail']);
$router->get('/resend-verification', [\App\Controllers\AuthController::class, 'resendVerification']);
$router->post('/api/auth/google', [\App\Controllers\AuthController::class, 'googleLogin']);

// App / Post Routes
$router->get('/', [\App\Controllers\PostController::class, 'index']);
$router->post('/post/create', [\App\Controllers\PostController::class, 'create']);
$router->get('/post/delete/{id}', [\App\Controllers\PostController::class, 'delete']);
$router->get('/explore', [\App\Controllers\PostController::class, 'explore']);
$router->get('/bookmarks', [\App\Controllers\PostController::class, 'bookmarks']);

// User Routes
$router->get('/profile/{username}', [\App\Controllers\UserController::class, 'showProfile']);
$router->get('/edit-profile', [\App\Controllers\UserController::class, 'showEditProfile']);
$router->post('/edit-profile', [\App\Controllers\UserController::class, 'updateProfile']);
$router->get('/user/block/{id}', [\App\Controllers\UserController::class, 'block']);
$router->get('/settings', [\App\Controllers\UserController::class, 'settings']);
$router->get('/terms', [\App\Controllers\UserController::class, 'terms']);
$router->get('/privacy', [\App\Controllers\UserController::class, 'privacy']);

// AJAX APIs
$router->post('/api/post/like', [\App\Controllers\PostController::class, 'like']);
$router->post('/api/post/unlike', [\App\Controllers\PostController::class, 'unlike']);
$router->post('/api/post/bookmark', [\App\Controllers\PostController::class, 'bookmark']);
$router->post('/api/post/comment', [\App\Controllers\PostController::class, 'addComment']);
$router->post('/api/comment/delete', [\App\Controllers\PostController::class, 'deleteComment']);
$router->post('/api/posts/load', [\App\Controllers\PostController::class, 'loadPosts']);
$router->post('/api/report', [\App\Controllers\PostController::class, 'report']);

$router->post('/api/user/follow', [\App\Controllers\UserController::class, 'follow']);
$router->post('/api/user/unfollow', [\App\Controllers\UserController::class, 'unfollow']);
$router->post('/api/user/unblock', [\App\Controllers\UserController::class, 'unblock']);
$router->post('/api/user/search', [\App\Controllers\UserController::class, 'search']);
$router->post('/api/user/delete-account', [\App\Controllers\UserController::class, 'deleteAccount']);
$router->post('/api/notification/read', [\App\Controllers\UserController::class, 'readNotifications']);

$router->post('/api/message/send', [\App\Controllers\MessageController::class, 'send']);
$router->post('/api/message/get', [\App\Controllers\MessageController::class, 'get']);

// Admin Routes
$router->get('/admin', [\App\Controllers\AdminController::class, 'index']);
$router->get('/admin/suspend/{id}', [\App\Controllers\AdminController::class, 'suspend']);
$router->get('/admin/unsuspend/{id}', [\App\Controllers\AdminController::class, 'unsuspend']);
$router->get('/admin/dismiss-report/{id}', [\App\Controllers\AdminController::class, 'dismissReport']);

// Resolve route
$router->resolve($request);
