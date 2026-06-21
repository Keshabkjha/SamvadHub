<div class="auth-page">
    <div class="auth-card" style="text-align:center; max-width:420px;">
        <div style="font-size:4rem; margin-bottom:16px;">🚫</div>
        <h1 style="font-size:1.5rem; margin-bottom:8px;">Account Suspended</h1>
        <p style="color:var(--text-muted); font-size:var(--font-size-sm); line-height:1.7; margin-bottom:24px;">
            Your account has been suspended due to a violation of our
            <a href="/terms" style="color:var(--brand-primary);">Community Guidelines</a>.
            If you believe this is a mistake, please contact our support team.
        </p>

        <div style="background:var(--bg-input); border-radius:var(--radius-lg); padding:16px; margin-bottom:24px; text-align:left;">
            <div style="font-size:var(--font-size-sm); color:var(--text-secondary);">
                <div style="margin-bottom:8px;">
                    <i class="bi bi-person-fill me-2 text-brand"></i>
                    <strong><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></strong>
                </div>
                <div>
                    <i class="bi bi-envelope-fill me-2 text-brand"></i>
                    <?= e($user['email']) ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center">
            <a href="mailto:support@samvadhub.com?subject=Account+Appeal&body=My+username+is+<?= urlencode($user['username']) ?>"
               class="btn btn-primary">
                <i class="bi bi-envelope-fill me-1"></i>Contact Support
            </a>
            <a href="/logout" class="btn btn-outline-primary text-decoration-none">
                <i class="bi bi-box-arrow-right me-1"></i>Sign Out
            </a>
        </div>
    </div>
</div>
