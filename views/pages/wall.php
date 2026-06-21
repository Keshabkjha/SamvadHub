<?php
// Variables are extracted in Response::renderView
?>
<div class="feed-layout">
    <!-- Main Feed Column -->
    <div class="feed-column">
        <?php showError('post_img'); ?>
        <?php showError('general'); ?>

        <?php if (isset($_GET['new_post_added'])): ?>
            <div class="alert alert-success mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Your post has been shared!
            </div>
        <?php endif; ?>

        <!-- Skeleton loaders (shown initially, hidden after load) -->
        <div id="skeleton-feed">
            <?php for ($s = 0; $s < 2; $s++): ?>
                <div class="skeleton-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="skeleton" style="width:44px;height:44px;border-radius:50%;flex-shrink:0;"></div>
                        <div style="flex:1">
                            <div class="skeleton mb-2" style="width:140px;height:14px;"></div>
                            <div class="skeleton" style="width:90px;height:11px;"></div>
                        </div>
                    </div>
                    <div class="skeleton mb-2" style="width:100%;height:260px;border-radius:var(--radius-md);"></div>
                    <div class="skeleton mt-2" style="width:80px;height:11px;"></div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Actual posts -->
        <div id="feed-posts" style="display:none;">
        <?php if (count($posts) < 1): ?>
            <div class="empty-state">
                <i class="bi bi-newspaper empty-state-icon"></i>
                <div class="empty-state-title">Nothing in your feed yet</div>
                <div class="empty-state-desc">Follow some people or create your first post to get started!</div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addpost">
                    <i class="bi bi-plus-lg"></i> Create Post
                </button>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post):
            $likes    = \App\Models\Social::getLikes((int)$post['id']);
            $comments = \App\Models\Social::getComments((int)$post['id']);
            include dirname(__DIR__) . '/partials/post_card.php';
        endforeach; ?>
        </div><!-- /#feed-posts -->

        <!-- Load More Button -->
        <?php if (count($posts) >= 20): ?>
            <div class="text-center py-4">
                <button id="load-more-btn" class="btn btn-outline-primary" data-offset="20" type="button">
                    <i class="bi bi-arrow-down-circle"></i> Load More
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Current User Card -->
        <div class="sidebar-card">
            <a href="/profile/<?= e($user['username']) ?>" class="d-flex align-items-center gap-3 text-decoration-none mb-3">
                <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>" class="avatar avatar-md" alt="Profile">
                <div>
                    <div style="font-size:var(--font-size-sm); font-weight:700; color:var(--text-primary);">
                        <?= e($user['first_name']) ?> <?= e($user['last_name']) ?>
                    </div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">@<?= e($user['username']) ?></div>
                </div>
            </a>
            <?php if (!empty($user['bio'])): ?>
                <p style="font-size:var(--font-size-xs); color:var(--text-secondary); margin:0 0 12px;"><?= e($user['bio']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Follow Suggestions -->
        <?php if (count($follow_suggestions) > 0): ?>
        <div class="sidebar-card">
            <div class="sidebar-card-title">
                <i class="bi bi-people-fill me-2 text-brand"></i>People You May Know
            </div>
            <?php foreach ($follow_suggestions as $suser): ?>
                <div class="user-item">
                    <a href="/profile/<?= e($suser['username']) ?>" class="user-item-info text-decoration-none">
                        <img src="/assets/images/profile/<?= e($suser['profile_pic']) ?>"
                             class="avatar avatar-sm" alt="<?= e($suser['first_name']) ?>" loading="lazy">
                        <div>
                            <div class="user-item-name"><?= e($suser['first_name']) ?> <?= e($suser['last_name']) ?></div>
                            <div class="user-item-handle">@<?= e($suser['username']) ?></div>
                        </div>
                    </a>
                    <button class="btn btn-primary btn-sm followbtn" data-user-id="<?= (int)$suser['id'] ?>" type="button">
                        Follow
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Footer links -->
        <div style="padding:0 4px; font-size:var(--font-size-xs); color:var(--text-muted);">
            <a href="/terms" style="color:var(--text-muted);">Terms</a> ·
            <a href="/privacy" style="color:var(--text-muted);">Privacy</a> ·
            <a href="/" style="color:var(--text-muted);">Help</a>
            <div style="margin-top:8px;">© <?= date('Y') ?> SamvadHub</div>
        </div>
    </div>
</div>

<script>
// Show feed after brief animation delay
setTimeout(function() {
    document.getElementById('skeleton-feed').style.display = 'none';
    document.getElementById('feed-posts').style.display = 'block';
}, 400);
</script>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag-fill me-2 text-danger"></i>Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color:var(--text-secondary); font-size:var(--font-size-sm);">Why are you reporting this?</p>
                <div class="d-flex flex-column gap-2" id="report-reasons">
                    <?php foreach (['Spam or misleading', 'Inappropriate content', 'Harassment or bullying', 'Hate speech', 'Misinformation', 'Other'] as $reason): ?>
                        <button class="btn btn-outline-primary report-reason-btn" type="button" data-reason="<?= e($reason) ?>" style="justify-content:flex-start;">
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