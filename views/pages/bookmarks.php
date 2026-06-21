<div style="max-width:680px; margin:0 auto; padding:24px 16px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 style="font-size:1.5rem; font-weight:800; margin:0;" class="gradient-heading">
            <i class="bi bi-bookmark-fill me-2"></i>Bookmarks
        </h1>
        <div style="font-size:var(--font-size-sm); color:var(--text-muted);">
            <?= count($posts) ?> saved post<?= count($posts) !== 1 ? 's' : '' ?>
        </div>
    </div>

    <?php if (count($posts) < 1): ?>
        <div class="empty-state">
            <i class="bi bi-bookmark empty-state-icon"></i>
            <div class="empty-state-title">No bookmarks yet</div>
            <div class="empty-state-desc">
                Save posts by tapping the bookmark icon on any post. You can find them all here.
            </div>
            <a href="/" class="btn btn-primary mt-3 text-decoration-none">
                <i class="bi bi-house-fill me-1"></i>Go to Feed
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post):
            $likes    = \App\Models\Social::getLikes((int)$post['id']);
            $comments = \App\Models\Social::getComments((int)$post['id']);
            include dirname(__DIR__) . '/partials/post_card.php';
        endforeach; ?>
    <?php endif; ?>
</div>
