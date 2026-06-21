<?php if (isset($_SESSION['Auth'])): ?>

<!-- ─── Add Post Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="addpost" tabindex="-1" aria-labelledby="addPostTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPostTitle">
                    <i class="bi bi-plus-square-fill me-2 text-brand"></i>Create Post
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Post author preview -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
                         class="avatar avatar-md" alt="<?= e($user['first_name']) ?>">
                    <div>
                        <div style="font-weight:600; font-size:var(--font-size-sm);">
                            <?= e($user['first_name']) ?> <?= e($user['last_name']) ?>
                        </div>
                        <div style="font-size:var(--font-size-xs); color:var(--text-muted);">@<?= e($user['username']) ?></div>
                    </div>
                </div>

                <form method="post" action="/post/create" enctype="multipart/form-data" id="addPostForm">
                    <?= csrf_field() ?>

                    <!-- Image preview -->
                    <img src="" id="post_img_preview" class="post-preview-image" alt="Preview" style="display:none;">

                    <!-- Text input -->
                    <textarea name="post_text" id="post_text_input" class="post-textarea"
                              rows="4" placeholder="What's on your mind, <?= e($user['first_name']) ?>?"
                              maxlength="2000"></textarea>
                    <div style="text-align:right; font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px;">
                        <span id="charCount">0</span>/2000
                    </div>

                    <!-- Image upload -->
                    <div class="mt-3">
                        <label for="select_post_img" style="font-size:var(--font-size-sm); color:var(--text-secondary); font-weight:500; display:block; margin-bottom:8px;">
                            <i class="bi bi-image me-1"></i>Add Photo (optional)
                        </label>
                        <input class="form-control" name="post_img" type="file"
                               id="select_post_img" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <?php showError('post_img'); ?>
                    <?php showError('general'); ?>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="postSubmitBtn">
                            <i class="bi bi-send-fill me-1"></i> Share Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ─── Notifications Sidebar ──────────────────────────────────── -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="notification_sidebar" aria-labelledby="notifLabel" style="width:360px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="notifLabel">
            <i class="bi bi-bell-fill me-2 text-brand"></i>Notifications
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" style="padding:8px 16px;">
        <?php
        $notifications = \App\Models\Notification::getForCurrentUser();
        if (count($notifications) < 1):
        ?>
            <div class="empty-state">
                <i class="bi bi-bell empty-state-icon"></i>
                <div class="empty-state-title">No notifications yet</div>
                <div class="empty-state-desc">When people interact with your posts, you'll see it here.</div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $not):
                $fuser  = \App\Models\User::getById((int)$not['from_user_id']);
                $is_read = (int)$not['read_status'] > 0;
                if (empty($fuser)) continue;
            ?>
            <div class="notif-item <?= !$is_read ? 'unread' : '' ?>"
                 <?= $not['post_id'] ? 'data-bs-toggle="modal" data-bs-target="#postview' . (int)$not['post_id'] . '"' : '' ?>>
                <img src="/assets/images/profile/<?= e($fuser['profile_pic']) ?>"
                     class="avatar avatar-sm flex-shrink-0" alt="<?= e($fuser['first_name']) ?>">
                <div style="flex:1; min-width:0;">
                    <div class="notif-text">
                        <strong><a href="/profile/<?= e($fuser['username']) ?>" style="color:var(--brand-primary); text-decoration:none;">
                            <?= e($fuser['first_name']) ?> <?= e($fuser['last_name']) ?>
                        </a></strong>
                        <?= e($not['message']) ?>
                    </div>
                    <div class="notif-time"><?= show_time($not['created_at']) ?></div>
                </div>
                <?php if (!$is_read): ?><div class="notif-dot flex-shrink-0"></div><?php endif; ?>
                <?php if ($not['read_status'] == 2): ?>
                    <span class="badge badge-danger" style="font-size:10px; flex-shrink:0;">Deleted</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<!-- ─── Messages Sidebar ────────────────────────────────────────── -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="message_sidebar" aria-labelledby="msgLabel" style="width:360px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="msgLabel">
            <i class="bi bi-chat-dots-fill me-2 text-brand"></i>Messages
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="chatlist" style="padding:8px 16px;">
        <div class="empty-state" style="padding:40px 16px;">
            <i class="bi bi-chat-dots empty-state-icon"></i>
            <div class="empty-state-title">Loading messages…</div>
        </div>
    </div>
</div>


<!-- ─── Chat Box Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="chatbox" tabindex="-1" aria-labelledby="chatboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:440px;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2 flex-fill">
                    <a href="#" id="cplink" class="text-decoration-none d-flex align-items-center gap-2">
                        <img src="assets/images/profile/default_profile.jpg"
                             id="chatter_pic" class="avatar avatar-sm" alt="Chat user">
                        <div>
                            <div style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-primary);" id="chatter_name">
                                Loading…
                            </div>
                            <div style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                @<span id="chatter_username">loading</span>
                            </div>
                        </div>
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div id="user_chat">
                    <div class="d-flex justify-content-center p-4">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-block" style="padding:0;">
                <div id="blerror" style="display:none; padding:10px 16px;">
                    <div class="alert alert-danger mb-0" style="font-size:var(--font-size-sm);">
                        <i class="bi bi-x-octagon-fill me-2"></i>You can no longer message this user.
                    </div>
                </div>
                <div class="d-flex gap-2 p-3" id="msgsender">
                    <input type="text" class="comment-input" id="msginput"
                           placeholder="Type a message…" maxlength="2000"
                           style="flex:1;" autocomplete="off">
                    <button class="btn btn-primary btn-sm" id="sendmsg" type="button" data-user-id="0">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ─── Toast Container ─────────────────────────────────────────── -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<?php endif; ?>

<!-- ─── Cookie Consent Banner ────────────────────────────────────── -->
<div id="cookie-consent-banner" class="cookie-banner hidden" role="dialog" aria-labelledby="cookieTitle" aria-describedby="cookieDesc">
    <div class="cookie-banner-content">
        <div class="cookie-banner-icon" aria-hidden="true">
            <i class="bi bi-cookie"></i>
        </div>
        <div class="cookie-banner-text">
            <h5 id="cookieTitle">Cookie Consent</h5>
            <p id="cookieDesc">We use cookies to enhance your experience, analyze site traffic, and deliver personalized content. By clicking "Accept All", you agree to our use of cookies as detailed in our <a href="/privacy">Privacy Policy</a>.</p>
        </div>
    </div>
    <div class="cookie-banner-actions">
        <button id="cookie-decline-btn" class="btn btn-outline-primary btn-sm">Decline</button>
        <button id="cookie-accept-btn" class="btn btn-primary btn-sm">Accept All</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (!localStorage.getItem("cookie_consent")) {
        var banner = document.getElementById("cookie-consent-banner");
        if (banner) {
            banner.classList.remove("hidden");
            
            document.getElementById("cookie-accept-btn").addEventListener("click", function() {
                localStorage.setItem("cookie_consent", "accepted");
                banner.classList.add("hidden");
            });

            document.getElementById("cookie-decline-btn").addEventListener("click", function() {
                localStorage.setItem("cookie_consent", "declined");
                banner.classList.add("hidden");
            });
        }
    }
});
</script>

<!-- ─── Scripts ─────────────────────────────────────────────────── -->
<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/jquery-3.6.0.min.js"></script>
<script src="/assets/js/jquery.timeago.js"></script>
<script src="/assets/js/modules/ui.js?v=<?= time() ?>"></script>
<script src="/assets/js/modules/theme.js?v=<?= time() ?>"></script>
<script src="/assets/js/modules/posts.js?v=<?= time() ?>"></script>
<script src="/assets/js/modules/social.js?v=<?= time() ?>"></script>
<script src="/assets/js/modules/chat.js?v=<?= time() ?>"></script>
<script src="/assets/js/modules/search.js?v=<?= time() ?>"></script>

</body>
</html>
