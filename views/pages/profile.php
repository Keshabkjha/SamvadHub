<?php
// Variables are extracted in Response::renderView
$is_own    = ($user['id'] === $puser['id']);
$is_followed  = !$is_own && \App\Models\Social::checkFollowStatus((int)$puser['id']);
$is_blocked   = !$is_own && \App\Models\Social::checkBlockStatus((int)$user['id'], (int)$puser['id']);
$is_blocked_by = !$is_own && \App\Models\Social::checkBlockStatus((int)$puser['id'], (int)$user['id']);
$blocked_either = $is_blocked || $is_blocked_by;
$followers = \App\Models\Social::getFollowers((int)$puser['id']);
$following = \App\Models\Social::getFollowing((int)$puser['id']);
$posts     = \App\Models\Post::getByUserId((int)$puser['id']);
?>
<div style="max-width:900px; margin:0 auto; padding:24px 16px;">

    <!-- Profile Header Card -->
    <div class="post-card" style="padding:24px; margin-bottom:20px;">
        <div class="profile-meta">

            <!-- Avatar -->
            <div style="flex-shrink:0;">
                <img src="/assets/images/profile/<?= e($puser['profile_pic']) ?>"
                     alt="<?= e($puser['first_name']) ?> profile picture"
                     class="avatar avatar-xxl">
            </div>

            <!-- Info -->
            <div style="flex:1; min-width:0;">
                <!-- Name + badges -->
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 style="font-size:1.4rem; font-weight:800; margin:0; color:var(--text-primary);">
                        <?= e($puser['first_name']) ?> <?= e($puser['last_name']) ?>
                    </h1>
                    <?php if (!empty($puser['is_admin']) && $puser['is_admin']): ?>
                        <span class="badge badge-primary" style="font-size:10px;">Admin</span>
                    <?php endif; ?>
                    <?php if ($puser['ac_status'] == 2): ?>
                        <span class="badge badge-danger" style="font-size:10px;">Suspended</span>
                    <?php endif; ?>
                </div>

                <div style="color:var(--text-muted); font-size:var(--font-size-sm); margin-bottom:10px;">
                    @<?= e($puser['username']) ?>
                    <?php $gender_map = [1 => '· Male', 2 => '· Female', 0 => '· Other'];
                    echo isset($gender_map[$puser['gender']]) ? e($gender_map[$puser['gender']]) : ''; ?>
                </div>

                <!-- Bio -->
                <?php if (!empty($puser['bio'])): ?>
                    <p class="profile-bio"><?= e($puser['bio']) ?></p>
                <?php endif; ?>

                <!-- Website -->
                <?php if (!empty($puser['website'])): ?>
                    <div class="profile-website mb-2">
                        <i class="bi bi-link-45deg me-1"></i>
                        <a href="<?= e($puser['website']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= e(parse_url($puser['website'], PHP_URL_HOST) ?: $puser['website']) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="profile-stats">
                    <button class="profile-stat" data-bs-toggle="modal" data-bs-target="#followersModal" type="button">
                        <span class="profile-stat-count"><?= count($followers) ?></span>
                        <span class="profile-stat-label">Followers</span>
                    </button>
                    <button class="profile-stat" data-bs-toggle="modal" data-bs-target="#followingModal" type="button">
                        <span class="profile-stat-count"><?= count($following) ?></span>
                        <span class="profile-stat-label">Following</span>
                    </button>
                    <div class="profile-stat">
                        <span class="profile-stat-count"><?= count($posts) ?></span>
                        <span class="profile-stat-label">Posts</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php if ($is_own): ?>
                        <a href="/edit-profile" class="btn btn-outline-primary btn-sm text-decoration-none">
                            <i class="bi bi-pencil-fill me-1"></i>Edit Profile
                        </a>
                        <a href="/bookmarks" class="btn btn-outline-primary btn-sm text-decoration-none">
                            <i class="bi bi-bookmark-fill me-1"></i>Bookmarks
                        </a>

                    <?php elseif ($is_blocked): ?>
                        <button class="btn btn-sm btn-outline-primary unblockbtn" data-user-id="<?= (int)$puser['id'] ?>" type="button">
                            <i class="bi bi-slash-circle me-1"></i>Unblock
                        </button>

                    <?php elseif (!$blocked_either): ?>
                        <?php if ($is_followed): ?>
                            <button class="btn btn-sm btn-outline-primary unfollowbtn" data-user-id="<?= (int)$puser['id'] ?>" type="button">
                                <i class="bi bi-check2 me-1"></i>Following
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-primary followbtn" data-user-id="<?= (int)$puser['id'] ?>" type="button">
                                <i class="bi bi-person-plus-fill me-1"></i>Follow
                            </button>
                        <?php endif; ?>

                        <!-- Message Button -->
                        <button class="btn btn-sm btn-outline-primary" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#message_sidebar"
                                onclick="popchat(<?= (int)$puser['id'] ?>)">
                            <i class="bi bi-chat-fill me-1"></i>Message
                        </button>

                        <!-- More Options -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary" type="button"
                                    data-bs-toggle="dropdown" aria-label="More options">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-danger"
                                       href="/user/block/<?= (int)$puser['id'] ?>?username=<?= urlencode($puser['username']) ?>"
                                       onclick="return confirm('Block <?= e(addslashes($puser['first_name'])) ?>? They will not be able to see your posts or message you.')">
                                        <i class="bi bi-ban"></i> Block User
                                    </a>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger report-btn"
                                            data-type="user" data-id="<?= (int)$puser['id'] ?>" type="button">
                                        <i class="bi bi-flag-fill"></i> Report User
                                    </button>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocked Notice -->
    <?php if ($is_blocked_by && !$is_own): ?>
        <div class="empty-state">
            <i class="bi bi-slash-circle empty-state-icon"></i>
            <div class="empty-state-title">You can't view this content</div>
            <div class="empty-state-desc">This user has restricted their account.</div>
        </div>

    <?php else: ?>

        <!-- Posts Grid -->
        <?php if (count($posts) > 0): ?>
            <?php
            // Separate image posts vs text-only posts
            $image_posts  = array_filter($posts, fn($p) => !empty($p['post_img']));
            $text_posts   = array_filter($posts, fn($p) => empty($p['post_img']));
            ?>

            <?php if (!empty($image_posts)): ?>
                <div class="profile-gallery" style="margin-bottom:20px;">
                    <?php foreach ($image_posts as $post):
                        $likes    = \App\Models\Social::getLikes((int)$post['id']);
                        $comments = \App\Models\Social::getComments((int)$post['id']);
                    ?>
                        <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryPost<?= (int)$post['id'] ?>">
                            <img src="/assets/images/posts/<?= e($post['post_img']) ?>"
                                 alt="Post" loading="lazy">
                            <div class="explore-item-overlay">
                                <span><i class="bi bi-heart-fill me-1"></i><?= count($likes) ?></span>
                                <span><i class="bi bi-chat-fill me-1"></i><?= count($comments) ?></span>
                            </div>
                        </div>

                        <!-- Gallery Post Modal -->
                        <div class="modal fade" id="galleryPost<?= (int)$post['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content" style="border-radius:var(--radius-xl); overflow:hidden;">
                                    <div class="d-md-flex" style="min-height:400px;">
                                        <div class="col-md-7" style="background:#000; display:flex; align-items:center;">
                                            <img src="/assets/images/posts/<?= e($post['post_img']) ?>"
                                                 style="max-height:88vh; width:100%; object-fit:contain;" alt="Post">
                                        </div>
                                        <div class="col-md-5 d-flex flex-column" style="max-height:88vh;">
                                            <div class="post-header" style="border-bottom:1px solid var(--border-light);">
                                                <a href="/profile/<?= e($puser['username']) ?>" class="post-user-info">
                                                    <img src="/assets/images/profile/<?= e($puser['profile_pic']) ?>" class="avatar avatar-md" alt="">
                                                    <div>
                                                        <span class="post-username"><?= e($puser['first_name']) ?> <?= e($puser['last_name']) ?></span>
                                                        <span class="post-handle"><?= show_time($post['created_at']) ?></span>
                                                    </div>
                                                </a>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <?php if (!empty($post['post_text'])): ?>
                                                <div style="padding:12px 16px; border-bottom:1px solid var(--border-light); font-size:var(--font-size-sm);"><?= link_tags_mentions($post['post_text']) ?></div>
                                            <?php endif; ?>
                                            <div style="flex:1; overflow-y:auto; padding:8px 0;" id="gcs<?= (int)$post['id'] ?>">
                                                <?php if (empty($comments)): ?>
                                                    <div class="empty-state" style="padding:30px 16px;">
                                                        <i class="bi bi-chat empty-state-icon" style="font-size:1.5rem;"></i>
                                                        <div class="empty-state-desc">No comments yet</div>
                                                    </div>
                                                <?php endif; ?>
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
                                                    <?php $liked = \App\Models\Social::checkLikeStatus((int)$post['id']); ?>
                                                    <button class="action-btn <?= $liked ? 'unlike_btn liked' : 'like_btn' ?>"
                                                            data-post-id="<?= (int)$post['id'] ?>" type="button">
                                                        <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                                        <span id="likecount<?= (int)$post['id'] ?>"><?= count($likes) ?></span>
                                                    </button>
                                                </div>
                                                <?php if (!$blocked_either): ?>
                                                <div class="comment-input-row" style="padding:10px 16px;">
                                                    <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>" class="avatar avatar-sm flex-shrink-0" alt="">
                                                    <input type="text" class="comment-input" placeholder="Add a comment…"
                                                           data-post-id="<?= (int)$post['id'] ?>"
                                                           data-cs="gcs<?= (int)$post['id'] ?>">
                                                    <button class="btn btn-primary btn-sm add-comment"
                                                            data-cs="gcs<?= (int)$post['id'] ?>"
                                                            data-post-id="<?= (int)$post['id'] ?>" type="button">Post</button>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Text-only posts -->
            <?php if (!empty($text_posts)): ?>
                <div style="margin-bottom:20px;">
                    <?php foreach ($text_posts as $post):
                        $likes    = \App\Models\Social::getLikes((int)$post['id']);
                        $comments = \App\Models\Social::getComments((int)$post['id']);
                        include dirname(__DIR__) . '/partials/post_card.php';
                    endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-grid empty-state-icon"></i>
                <div class="empty-state-title"><?= $is_own ? 'Share your first post' : 'No posts yet' ?></div>
                <div class="empty-state-desc">
                    <?= $is_own ? 'Your photos and stories will appear here.' : 'When they post, you\'ll see it here.' ?>
                </div>
                <?php if ($is_own): ?>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addpost" type="button">
                        <i class="bi bi-plus-lg me-1"></i>Create Post
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Followers Modal -->
<div class="modal fade" id="followersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:360px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Followers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:8px 16px;">
                <?php if (empty($followers)): ?>
                    <div class="empty-state" style="padding:32px 16px;">
                        <i class="bi bi-people empty-state-icon" style="font-size:2rem;"></i>
                        <div class="empty-state-desc">No followers yet</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($followers as $fl):
                        $fuser = \App\Models\User::getById((int)$fl['follower_id']);
                        if (empty($fuser)) continue;
                    ?>
                        <div class="user-item" style="padding:10px 0; border-bottom:1px solid var(--border-light);">
                            <a href="/profile/<?= e($fuser['username']) ?>" class="user-item-info text-decoration-none" data-bs-dismiss="modal">
                                <img src="/assets/images/profile/<?= e($fuser['profile_pic']) ?>" class="avatar avatar-sm" alt="">
                                <div>
                                    <div class="user-item-name"><?= e($fuser['first_name']) ?> <?= e($fuser['last_name']) ?></div>
                                    <div class="user-item-handle">@<?= e($fuser['username']) ?></div>
                                </div>
                            </a>
                            <?php if ($fuser['id'] !== (int)$user['id']): ?>
                                <?php if (\App\Models\Social::checkFollowStatus((int)$fuser['id'])): ?>
                                    <button class="btn btn-sm btn-outline-primary unfollowbtn" data-user-id="<?= (int)$fuser['id'] ?>" type="button">Following</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary followbtn" data-user-id="<?= (int)$fuser['id'] ?>" type="button">Follow</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Following Modal -->
<div class="modal fade" id="followingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:360px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Following</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:8px 16px;">
                <?php if (empty($following)): ?>
                    <div class="empty-state" style="padding:32px 16px;">
                        <i class="bi bi-people empty-state-icon" style="font-size:2rem;"></i>
                        <div class="empty-state-desc">Not following anyone yet</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($following as $fl):
                        $fuser = \App\Models\User::getById((int)$fl['user_id']);
                        if (empty($fuser)) continue;
                    ?>
                        <div class="user-item" style="padding:10px 0; border-bottom:1px solid var(--border-light);">
                            <a href="/profile/<?= e($fuser['username']) ?>" class="user-item-info text-decoration-none" data-bs-dismiss="modal">
                                <img src="/assets/images/profile/<?= e($fuser['profile_pic']) ?>" class="avatar avatar-sm" alt="">
                                <div>
                                    <div class="user-item-name"><?= e($fuser['first_name']) ?> <?= e($fuser['last_name']) ?></div>
                                    <div class="user-item-handle">@<?= e($fuser['username']) ?></div>
                                </div>
                            </a>
                            <?php if ($fuser['id'] !== (int)$user['id']): ?>
                                <?php if (\App\Models\Social::checkFollowStatus((int)$fuser['id'])): ?>
                                    <button class="btn btn-sm btn-outline-primary unfollowbtn" data-user-id="<?= (int)$fuser['id'] ?>" type="button">Following</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary followbtn" data-user-id="<?= (int)$fuser['id'] ?>" type="button">Follow</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal (same as wall.php) -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag-fill me-2 text-danger"></i>Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:var(--text-secondary); font-size:var(--font-size-sm);">Why are you reporting this?</p>
                <div class="d-flex flex-column gap-2">
                    <?php foreach (['Spam or misleading', 'Inappropriate content', 'Harassment or bullying', 'Hate speech', 'Misinformation', 'Other'] as $reason): ?>
                        <button class="btn btn-outline-primary report-reason-btn" type="button"
                                data-reason="<?= e($reason) ?>" style="justify-content:flex-start;">
                            <?= e($reason) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="report-type" value="">
                <input type="hidden" id="report-id" value="">
            </div>
        </div>
    </div>
</div>