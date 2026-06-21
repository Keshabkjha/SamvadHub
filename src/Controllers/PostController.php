<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Post;
use App\Models\Social;

class PostController {
    /**
     * GET / - Wall/Feed page
     */
    public function index(Request $request): void {
        requireAuth();
        $offset = max(0, (int)$request->get('page', 0)) * 20;
        $posts  = Post::filterPosts($offset, 20);
        $follow_suggestions = Social::filterFollowSuggestions();

        Response::renderView('wall', [
            'page_title' => 'Home',
            'posts' => $posts,
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * POST /post/create
     */
    public function create(Request $request): void {
        requireAuth();
        $request->verifyCsrf();
        
        $postImg = $request->file('post_img');
        if (Post::create($request->all(), $postImg)) {
            Response::redirect('/?new_post_added');
        } else {
            Response::redirect('/');
        }
    }

    /**
     * GET /post/delete/{id}
     */
    public function delete(Request $request): void {
        requireAuth();
        $postId = (int)$request->routeParam('id', 0);
        if ($postId > 0) {
            Post::delete($postId);
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        Response::redirect($ref);
    }

    /**
     * GET /explore
     */
    public function explore(Request $request): void {
        requireAuth();
        $search = trim($request->get('search', ''));
        if ($search !== '') {
            $posts = Post::searchPosts($search, 0, 50);
        } else {
            $posts = Post::getExplorePosts(0, 20);
        }
        $follow_suggestions = Social::filterFollowSuggestions();

        Response::renderView('explore', [
            'page_title' => 'Explore',
            'posts' => $posts,
            'search' => $search,
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * GET /bookmarks
     */
    public function bookmarks(Request $request): void {
        requireAuth();
        $posts = Social::getBookmarkedPosts();
        $follow_suggestions = Social::filterFollowSuggestions();

        Response::renderView('bookmarks', [
            'page_title' => 'Bookmarks',
            'posts' => $posts,
            'follow_suggestions' => $follow_suggestions
        ]);
    }

    /**
     * POST /api/post/like
     */
    public function like(Request $request): void {
        requireAuth();
        $postId = (int)$request->post('post_id', 0);
        if ($postId <= 0) {
            Response::json(['status' => false]);
        }
        $status = !Social::checkLikeStatus($postId) ? Social::like($postId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/post/unlike
     */
    public function unlike(Request $request): void {
        requireAuth();
        $postId = (int)$request->post('post_id', 0);
        if ($postId <= 0) {
            Response::json(['status' => false]);
        }
        $status = Social::checkLikeStatus($postId) ? Social::unlike($postId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/post/bookmark
     */
    public function bookmark(Request $request): void {
        requireAuth();
        $postId = (int)$request->post('post_id', 0);
        if ($postId <= 0) {
            Response::json(['status' => false]);
        }

        if (Social::isPostBookmarked($postId)) {
            Response::json(['status' => Social::unbookmarkPost($postId), 'action' => 'removed']);
        } else {
            Response::json(['status' => Social::bookmarkPost($postId), 'action' => 'added']);
        }
    }

    /**
     * POST /api/post/comment
     */
    public function addComment(Request $request): void {
        requireAuth();
        $postId  = (int)$request->post('post_id', 0);
        $comment = trim($request->post('comment', ''));

        if ($postId <= 0 || empty($comment)) {
            Response::json(['status' => false]);
        }

        if (Social::addComment($postId, $comment)) {
            $cuser = User::getById(User::currentId());
            
            $commentHtml = '<div class="comment-item d-flex align-items-start gap-2 p-2">'
                . '<img src="assets/images/profile/' . e($cuser['profile_pic']) . '" alt="' . e($cuser['username']) . '" height="36" width="36" class="rounded-circle border flex-shrink-0">'
                . '<div class="comment-body">'
                . '<a href="/profile/' . e($cuser['username']) . '" class="comment-username">@' . e($cuser['username']) . '</a>'
                . '<span class="comment-text">' . link_tags_mentions($comment) . '</span>'
                . '<span class="comment-time">just now</span>'
                . '</div></div>';

            Response::json(['status' => true, 'comment' => $commentHtml]);
        } else {
            Response::json(['status' => false]);
        }
    }

    /**
     * POST /api/comment/delete
     */
    public function deleteComment(Request $request): void {
        requireAuth();
        $commentId = (int)$request->post('comment_id', 0);
        $status = $commentId > 0 ? Social::deleteComment($commentId) : false;
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/posts/load
     */
    public function loadPosts(Request $request): void {
        requireAuth();
        $offset = max(0, (int)$request->post('offset', 0));
        $posts  = Post::filterPosts($offset, 10);
        $html   = '';

        $baseDir = dirname(__DIR__, 2) . '/views';

        foreach ($posts as $post) {
            $likes    = Social::getLikes((int)$post['id']);
            $comments = Social::getComments((int)$post['id']);
            ob_start();
            include "{$baseDir}/partials/post_card.php";
            $html .= ob_get_clean();
        }

        Response::json([
            'status'   => true,
            'html'     => $html,
            'has_more' => count($posts) >= 10
        ]);
    }

    /**
     * POST /api/report
     */
    public function report(Request $request): void {
        requireAuth();
        $type   = $request->post('type', '');
        $id     = (int)$request->post('id', 0);
        $reason = trim($request->post('reason', 'No reason provided'));

        if ($id <= 0 || !in_array($type, ['user', 'post'])) {
            Response::json(['status' => false]);
        }

        if ($type === 'user') {
            Response::json(['status' => Social::reportUser($id, $reason)]);
        } else {
            Response::json(['status' => Social::reportPost($id, $reason)]);
        }
    }
}
