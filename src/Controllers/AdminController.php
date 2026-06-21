<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Post;
use App\Models\Social;

class AdminController {
    /**
     * GET /admin
     */
    public function index(Request $request): void {
        requireAuth();
        if (!User::isAdmin()) {
            http_response_code(403);
            Response::renderView('404', ['page_title' => 'Access Denied']);
        }

        $section      = $request->get('admin', 'dashboard');
        $total_users  = User::getTotalCount();
        $total_posts  = Post::getTotalCount();
        $reports      = Social::getPendingReports();
        $recent_users = User::getAllAdmin(0, 20);

        Response::renderView('admin', [
            'page_title' => 'Admin Panel',
            'section'      => $section,
            'total_users'  => $total_users,
            'total_posts'  => $total_posts,
            'reports'      => $reports,
            'recent_users' => $recent_users
        ], false);
    }

    /**
     * GET /admin/suspend/{id}
     */
    public function suspend(Request $request): void {
        requireAuth();
        if (!User::isAdmin()) {
            http_response_code(403);
            die('Access Denied');
        }

        $uid  = (int)$request->routeParam('id', 0);
        $from = $request->get('from', 'dashboard');
        if ($uid > 0) {
            User::block($uid);
        }
        
        Response::redirect("/admin?admin={$from}");
    }

    /**
     * GET /admin/unsuspend/{id}
     */
    public function unsuspend(Request $request): void {
        requireAuth();
        if (!User::isAdmin()) {
            http_response_code(403);
            die('Access Denied');
        }

        $uid = (int)$request->routeParam('id', 0);
        if ($uid > 0) {
            User::unblock($uid);
        }

        Response::redirect('/admin?admin=dashboard');
    }

    /**
     * GET /admin/dismiss-report/{id}
     */
    public function dismissReport(Request $request): void {
        requireAuth();
        if (!User::isAdmin()) {
            http_response_code(403);
            die('Access Denied');
        }

        $rid = (int)$request->routeParam('id', 0);
        if ($rid > 0) {
            Social::dismissReport($rid);
        }

        Response::redirect('/admin?admin=reports');
    }
}
