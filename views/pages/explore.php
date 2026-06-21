<?php
// views/pages/explore.php
$search = $search ?? '';
?>
<div style="max-width:900px; margin:0 auto; padding:24px 16px;">
    <!-- Page Header & Title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 style="font-size:1.5rem; font-weight:800; margin:0;" class="gradient-heading">
            <i class="bi bi-compass-fill me-2"></i>Explore
        </h1>
        <div style="font-size:var(--font-size-sm); color:var(--text-muted);">
            Discover trending posts and hashtags
        </div>
    </div>

    <!-- Explore Search Input -->
    <div class="mb-4">
        <form action="/explore" method="get" class="search-wrapper" style="max-width:100%; width:100%;">
            <i class="bi bi-search search-icon"></i>
            <input class="search-input" type="search" name="search" value="<?= e($search) ?>" placeholder="Search posts, hashtags..." aria-label="Search posts">
        </form>
    </div>

    <?php if (count($posts) < 1): ?>
        <?php if ($search !== ''): ?>
            <!-- Search Empty State -->
            <div class="empty-state">
                <i class="bi bi-search empty-state-icon"></i>
                <div class="empty-state-title">No posts found</div>
                <div class="empty-state-desc">We couldn't find any posts matching "<strong><?= e($search) ?></strong>". Try searching for other topics or tags!</div>
                <a class="btn btn-outline-primary mt-3" href="/explore">
                    <i class="bi bi-x-lg me-1"></i>Clear Search
                </a>
            </div>
        <?php else: ?>
            <!-- Default Empty State -->
            <div class="empty-state">
                <i class="bi bi-compass empty-state-icon"></i>
                <div class="empty-state-title">Nothing to explore yet</div>
                <div class="empty-state-desc">Be the first to post something amazing!</div>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addpost" type="button">
                    <i class="bi bi-plus-lg me-1"></i>Create Post
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $image_posts = array_filter($posts, fn($p) => !empty($p['post_img']));
        $text_posts  = array_filter($posts, fn($p) => empty($p['post_img']));
        ?>

        <!-- Image grid -->
        <?php if (!empty($image_posts)): ?>
            <div class="explore-grid mb-4">
                <?php foreach ($image_posts as $post):
                    $likes    = \App\Models\Social::getLikes((int)$post['id']);
                    $comments = \App\Models\Social::getComments((int)$post['id']);
                    $liked    = \App\Models\Social::checkLikeStatus((int)$post['id']);
                ?>
                    <div class="explore-item" data-bs-toggle="modal" data-bs-target="#explorePost<?= (int)$post['id'] ?>">
                        <img src="/assets/images/posts/<?= e($post['post_img']) ?>" alt="Post" loading="lazy">
                        <div class="explore-item-overlay">
                            <span><i class="bi bi-heart-fill me-1"></i><?= count($likes) ?></span>
                            <span><i class="bi bi-chat-fill me-1"></i><?= count($comments) ?></span>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="explorePost<?= (int)$post['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content" style="border-radius:var(--radius-xl); overflow:hidden;">
                                <div class="d-md-flex" style="min-height:400px;">
                                    <div class="col-md-7" style="background:#000; display:flex; align-items:center;">
                                        <img src="/assets/images/posts/<?= e($post['post_img']) ?>"
                                             style="max-height:88vh; width:100%; object-fit:contain;" alt="Post">
                                    </div>
                                    <div class="col-md-5 d-flex flex-column" style="max-height:88vh;">
                                        <div class="post-header" style="border-bottom:1px solid var(--border-light);">
                                            <a href="/profile/<?= e($post['username']) ?>" class="post-user-info">
                                                <img src="/assets/images/profile/<?= e($post['profile_pic']) ?>" class="avatar avatar-md" alt="">
                                                <div>
                                                    <span class="post-username"><?= e($post['first_name']) ?> <?= e($post['last_name']) ?></span>
                                                    <span class="post-handle">@<?= e($post['username']) ?></span>
                                                </div>
                                            </a>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <?php if (!empty($post['post_text'])): ?>
                                            <div style="padding:12px 16px; border-bottom:1px solid var(--border-light); font-size:var(--font-size-sm);"><?= link_tags_mentions($post['post_text']) ?></div>
                                        <?php endif; ?>
                                        <div style="flex:1; overflow-y:auto; padding:8px 0;" id="ecs<?= (int)$post['id'] ?>">
                                            <?php foreach ($comments as $c):
                                                $cu = \App\Models\User::getById((int)$c['user_id']); ?>
                                                <div class="comment-item">
                                                    <img src="/assets/images/profile/<?= e($cu['profile_pic']) ?>" class="avatar avatar-sm flex-shrink-0" alt="">
                                                    <div class="comment-body">
                                                        <a href="/profile/<?= e($cu['username']) ?>" class="comment-username">@<?= e($cu['username']) ?></a>
                                                        <span class="comment-text"><?= link_tags_mentions($c['comment']) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div style="border-top:1px solid var(--border-light);">
                                            <div class="post-actions" style="border:none; padding:8px 16px;">
                                                <button class="action-btn <?= $liked ? 'unlike_btn liked' : 'like_btn' ?>"
                                                        data-post-id="<?= (int)$post['id'] ?>" type="button">
                                                    <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                                    <span id="likecount<?= (int)$post['id'] ?>"><?= count($likes) ?></span>
                                                </button>
                                                <button class="action-btn bookmark_btn <?= \App\Models\Social::isPostBookmarked((int)$post['id']) ? 'bookmarked' : '' ?>"
                                                        data-post-id="<?= (int)$post['id'] ?>" style="margin-left:auto;" type="button">
                                                    <i class="bi <?= \App\Models\Social::isPostBookmarked((int)$post['id']) ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                                                </button>
                                            </div>
                                            <div class="comment-input-row" style="padding:10px 16px;">
                                                <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>" class="avatar avatar-sm flex-shrink-0" alt="">
                                                <input type="text" class="comment-input" placeholder="Add a comment…"
                                                       data-post-id="<?= (int)$post['id'] ?>"
                                                       data-cs="ecs<?= (int)$post['id'] ?>">
                                                <button class="btn btn-primary btn-sm add-comment"
                                                        data-cs="ecs<?= (int)$post['id'] ?>"
                                                        data-post-id="<?= (int)$post['id'] ?>" type="button">Post</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Text posts -->
        <?php foreach ($text_posts as $post):
            $likes    = \App\Models\Social::getLikes((int)$post['id']);
            $comments = \App\Models\Social::getComments((int)$post['id']);
            include dirname(__DIR__) . '/partials/post_card.php';
        endforeach; ?>
    <?php endif; ?>
</div>
