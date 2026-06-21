<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Social;
use App\Helpers\ValidationHelper;
use App\Models\Notification;

class UserController {
    /**
     * GET /profile/{username}
     */
    public function showProfile(Request $request): void {
        requireAuth();
        $username = trim($request->routeParam('username', ''));
        if (empty($username)) {
            Response::redirect('/');
        }

        $puser = User::getByUsername($username);
        $follow_suggestions = Social::filterFollowSuggestions();

        if (empty($puser)) {
            Response::renderView('404', [
                'page_title' => 'User Not Found',
                'follow_suggestions' => $follow_suggestions
            ]);
        }

        Response::renderView('profile', [
            'page_title' => e($puser['first_name']) . ' ' . e($puser['last_name']),
            'puser' => $puser,
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * GET /edit-profile
     */
    public function showEditProfile(Request $request): void {
        requireAuth();
        $follow_suggestions = Social::filterFollowSuggestions();
        Response::renderView('edit_profile', [
            'page_title' => 'Edit Profile',
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * POST /edit-profile
     */
    public function updateProfile(Request $request): void {
        requireAuth();
        $request->verifyCsrf();

        $response = ValidationHelper::validateUpdate($request->all());
        $image = $request->file('profile_pic') ?? ['name' => '', 'error' => UPLOAD_ERR_NO_FILE];

        if ($response['status']) {
            if (User::updateProfile($request->all(), $image)) {
                Response::redirect('/edit-profile?success');
            } else {
                Response::redirect('/edit-profile');
            }
        } else {
            $_SESSION['error'] = $response;
            Response::redirect('/edit-profile');
        }
    }

    /**
     * GET /user/block/{id}
     */
    public function block(Request $request): void {
        requireAuth();
        $userId   = (int)$request->routeParam('id', 0);
        $username = $request->get('username', '');
        
        if ($userId > 0) {
            Social::block($userId);
        }

        if (!empty($username)) {
            Response::redirect('/profile/' . urlencode($username));
        } else {
            Response::redirect('/');
        }
    }

    /**
     * POST /api/user/follow
     */
    public function follow(Request $request): void {
        requireAuth();
        $userId = (int)$request->post('user_id', 0);
        $status = $userId > 0 ? Social::follow($userId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/user/unfollow
     */
    public function unfollow(Request $request): void {
        requireAuth();
        $userId = (int)$request->post('user_id', 0);
        $status = $userId > 0 ? Social::unfollow($userId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/user/unblock
     */
    public function unblock(Request $request): void {
        requireAuth();
        $userId = (int)$request->post('user_id', 0);
        $status = $userId > 0 ? Social::unblock($userId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/user/search
     */
    public function search(Request $request): void {
        requireAuth();
        $keyword = trim($request->post('keyword', ''));
        if (strlen($keyword) < 2) {
            Response::json(['status' => false]);
        }

        $data  = User::search($keyword);
        $users = '';

        foreach ($data as $fuser) {
            $users .= '<a href="/profile/' . e($fuser['username']) . '" class="search-result-item d-flex align-items-center gap-2 p-2 text-decoration-none">'
                . '<img src="assets/images/profile/' . e($fuser['profile_pic']) . '" alt="" height="40" width="40" class="rounded-circle border flex-shrink-0">'
                . '<div>'
                . '<div class="search-name">' . e($fuser['first_name']) . ' ' . e($fuser['last_name']) . '</div>'
                . '<div class="search-username">@' . e($fuser['username']) . '</div>'
                . '</div></a>';
        }

        if (count($data) > 0) {
            Response::json(['status' => true, 'users' => $users]);
        } else {
            Response::json(['status' => false, 'users' => '']);
        }
    }

    /**
     * POST /user/delete-account
     */
    public function deleteAccount(Request $request): void {
        requireAuth();
        $request->verifyCsrf();
        $userId = User::currentId();

        User::softDelete($userId);

        session_destroy();
        Response::redirect('/login?deleted');
    }

    /**
     * GET /settings
     */
    public function settings(Request $request): void {
        requireAuth();
        $follow_suggestions = Social::filterFollowSuggestions();
        Response::renderView('settings', [
            'page_title' => 'Settings',
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * GET /terms
     */
    public function terms(Request $request): void {
        $follow_suggestions = isset($_SESSION['Auth']) ? Social::filterFollowSuggestions() : [];
        Response::renderView('terms', [
            'page_title' => 'Terms of Service',
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * GET /privacy
     */
    public function privacy(Request $request): void {
        $follow_suggestions = isset($_SESSION['Auth']) ? Social::filterFollowSuggestions() : [];
        Response::renderView('privacy', [
            'page_title' => 'Privacy Policy',
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * POST /api/notification/read
     */
    public function readNotifications(Request $request): void {
        requireAuth();
        $request->verifyCsrf();
        $status = Notification::markAllAsRead();
        Response::json(['status' => $status]);
    }
}
