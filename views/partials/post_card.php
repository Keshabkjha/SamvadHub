<?php
/** @var array $post */
/** @var array $user */
/** @var array $likes */
/** @var array $comments */

$liked = \App\Models\Social::checkLikeStatus((int)$post['id']);
$bookmarked = \App\Models\Social::isPostBookmarked((int)$post['id']);
$is_own = ((int)$post['user_id'] === (int)$user['id']);
?>
<div class="post-card" id="post-<?= (int)$post['id'] ?>">
    <!-- Post Header -->
    <div class="post-header">
        <a href="/profile/<?= e($post['username']) ?>" class="post-user-info">
            <img src="/assets/images/profile/<?= e($post['profile_pic']) ?>"
                 alt="<?= e($post['first_name']) ?>"
                 class="avatar avatar-md" loading="lazy">
            <div>
                <span class="post-username"><?= e($post['first_name']) ?> <?= e($post['last_name']) ?></span>
                <span class="post-handle">@<?= e($post['username']) ?> · <?= show_time($post['created_at']) ?></span>
            </div>
        </a>
        <!-- Post Options -->
        <div class="dropdown">
            <button class="nav-icon" style="font-size:1rem; width:32px; height:32px;"
                    data-bs-toggle="dropdown" aria-label="Post options" type="button">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($is_own): ?>
                    <li>
                        <a class="dropdown-item text-danger"
                           href="/post/delete/<?= (int)$post['id'] ?>"
                           onclick="return confirm('Delete this post?')">
                            <i class="bi bi-trash-fill"></i> Delete Post
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <button class="dropdown-item text-danger report-btn"
                                data-type="post" data-id="<?= (int)$post['id'] ?>" type="button">
                            <i class="bi bi-flag-fill"></i> Report Post
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Post Caption (if text) -->
    <?php if (!empty(trim($post['post_text'] ?? ''))): ?>
        <div class="post-text"><?= link_tags_mentions($post['post_text']) ?></div>
    <?php endif; ?>

    <!-- Post Image -->
    <?php if (!empty($post['post_img'])): ?>
        <img src="/assets/images/posts/<?= e($post['post_img']) ?>"
             class="post-image" alt="Post by <?= e($post['first_name']) ?>"
             loading="lazy"
             data-bs-toggle="modal" data-bs-target="#postview<?= (int)$post['id'] ?>">
    <?php endif; ?>

    <!-- Post Actions -->
    <div class="post-actions">
        <!-- Like -->
        <button class="action-btn <?= $liked ? 'unlike_btn liked' : 'like_btn' ?>" data-post-id="<?= (int)$post['id'] ?>" aria-label="<?= $liked ? 'Unlike' : 'Like' ?>">
            <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            <span id="likecount<?= (int)$post['id'] ?>"><?= count($likes) ?></span>
        </button>

        <!-- Comment -->
        <button class="action-btn"
                data-bs-toggle="modal" data-bs-target="#postview<?= (int)$post['id'] ?>"
                aria-label="Comments">
            <i class="bi bi-chat"></i>
            <span><?= count($comments) ?></span>
        </button>

        <!-- Bookmark -->
        <button class="action-btn bookmark_btn <?= $bookmarked ? 'bookmarked' : '' ?>"
                data-post-id="<?= (int)$post['id'] ?>" aria-label="Bookmark"
                style="margin-left:auto;">
            <i class="bi bi-bookmark<?= $bookmarked ? '-fill' : '' ?>"></i>
        </button>
    </div>

    <!-- Inline Comment Input -->
    <div class="comment-input-row">
        <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
             alt="<?= e($user['first_name']) ?>" class="avatar avatar-sm flex-shrink-0" loading="lazy">
        <input type="text" class="comment-input"
               placeholder="Write a comment…"
               data-post-id="<?= (int)$post['id'] ?>"
               data-cs="comment-section<?= (int)$post['id'] ?>">
        <button class="btn btn-primary btn-sm add-comment"
                data-page="wall"
                data-cs="comment-section<?= (int)$post['id'] ?>"
                data-post-id="<?= (int)$post['id'] ?>"
                type="button">Post</button>
    </div>

    <!-- Latest 2 comments preview -->
    <?php if (count($comments) > 0):
        $preview_comments = array_slice($comments, 0, 2);
        foreach ($preview_comments as $c):
            $cu = \App\Models\User::getById((int)$c['user_id']);
            if (empty($cu)) continue;
    ?>
        <div class="comment-item">
            <img src="/assets/images/profile/<?= e($cu['profile_pic']) ?>"
                 alt="" class="avatar avatar-sm flex-shrink-0" loading="lazy">
            <div class="comment-body">
                <a href="/profile/<?= e($cu['username']) ?>" class="comment-username">@<?= e($cu['username']) ?></a>
                <span class="comment-text"><?= link_tags_mentions($c['comment']) ?></span>
                <span class="comment-time"><?= show_time($c['created_at']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (count($comments) > 2): ?>
        <div style="padding:8px 16px;">
            <button class="action-btn" style="font-size:var(--font-size-xs); color:var(--text-muted);"
                    data-bs-toggle="modal" data-bs-target="#postview<?= (int)$post['id'] ?>" type="button">
                View all <?= count($comments) ?> comments
            </button>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Post Detail Modal -->
<div class="modal fade" id="postview<?= (int)$post['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl); overflow:hidden;">
            <div class="d-md-flex" style="min-height:400px;">
                <!-- Image side -->
                <div class="col-md-7 col-sm-12" style="background:#000; display:flex; align-items:center; justify-content:center;">
                    <?php if (!empty($post['post_img'])): ?>
                        <img src="/assets/images/posts/<?= e($post['post_img']) ?>"
                             style="max-height:88vh; width:100%; object-fit:contain;" alt="Post">
                    <?php else: ?>
                        <div style="padding:60px; color:#fff; text-align:center; font-size:1.1rem;">
                            <?= link_tags_mentions($post['post_text']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Comments side -->
                <div class="col-md-5 col-sm-12 d-flex flex-column" style="max-height:88vh;">
                    <!-- Author -->
                    <div class="post-header" style="border-bottom:1px solid var(--border-light);">
                        <a href="/profile/<?= e($post['username']) ?>" class="post-user-info">
                            <img src="/assets/images/profile/<?= e($post['profile_pic']) ?>"
                                 alt="" class="avatar avatar-md">
                            <div>
                                <span class="post-username"><?= e($post['first_name']) ?> <?= e($post['last_name']) ?></span>
                                <span class="post-handle">@<?= e($post['username']) ?></span>
                            </div>
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Caption -->
                    <?php if (!empty(trim($post['post_text'] ?? ''))): ?>
                        <div style="padding:12px 16px; border-bottom:1px solid var(--border-light); font-size:var(--font-size-sm);">
                            <?= link_tags_mentions($post['post_text']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Comments scroll area -->
                    <div id="comment-section<?= (int)$post['id'] ?>"
                         style="flex:1; overflow-y:auto; padding:8px 0;">
                        <?php if (count($comments) < 1): ?>
                            <div class="empty-state" style="padding:40px 16px;">
                                <i class="bi bi-chat empty-state-icon" style="font-size:2rem;"></i>
                                <div class="empty-state-title" style="font-size:var(--font-size-sm);">No comments yet</div>
                                <div class="empty-state-desc">Be the first to comment!</div>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($comments as $comment):
                            $cuser = \App\Models\User::getById((int)$comment['user_id']);
                            if (empty($cuser)) continue;
                        ?>
                            <div class="comment-item">
                                <img src="/assets/images/profile/<?= e($cuser['profile_pic']) ?>"
                                     alt="" class="avatar avatar-sm flex-shrink-0">
                                <div class="comment-body">
                                    <a href="/profile/<?= e($cuser['username']) ?>" class="comment-username">@<?= e($cuser['username']) ?></a>
                                    <span class="comment-text"><?= link_tags_mentions($comment['comment']) ?></span>
                                    <span class="comment-time"><?= show_time($comment['created_at']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Actions & comment input -->
                    <div style="border-top:1px solid var(--border-light);">
                        <div class="post-actions" style="border:none; padding:8px 16px;">
                            <button class="action-btn <?= $liked ? 'unlike_btn liked' : 'like_btn' ?>" data-post-id="<?= (int)$post['id'] ?>" aria-label="<?= $liked ? 'Unlike' : 'Like' ?>" type="button">
                                <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                <span id="likecount<?= (int)$post['id'] ?>m"><?= count($likes) ?> likes</span>
                            </button>
                            <button class="action-btn bookmark_btn <?= $bookmarked ? 'bookmarked' : '' ?>"
                                    data-post-id="<?= (int)$post['id'] ?>" style="margin-left:auto;" type="button">
                                <i class="bi bi-bookmark<?= $bookmarked ? '-fill' : '' ?>"></i>
                            </button>
                        </div>
                        <div class="comment-input-row" style="padding:10px 16px;">
                            <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
                                 alt="" class="avatar avatar-sm flex-shrink-0">
                            <input type="text" class="comment-input"
                                   placeholder="Add a comment…"
                                   data-post-id="<?= (int)$post['id'] ?>"
                                   data-cs="comment-section<?= (int)$post['id'] ?>">
                            <button class="btn btn-primary btn-sm add-comment"
                                    data-cs="comment-section<?= (int)$post['id'] ?>"
                                    data-post-id="<?= (int)$post['id'] ?>"
                                    type="button">Post</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
