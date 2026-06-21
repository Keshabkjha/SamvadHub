<div style="max-width:640px; margin:0 auto; padding:24px 16px;">
    <h1 class="gradient-heading" style="font-size:1.5rem; margin-bottom:20px;">
        <i class="bi bi-gear-fill me-2"></i>Settings
    </h1>

    <!-- Account Settings -->
    <div class="post-card p-3 mb-3">
        <div class="sidebar-card-title">Account</div>
        <div class="d-flex flex-column gap-2">
            <a href="/edit-profile" class="d-flex align-items-center justify-content-between p-3 rounded text-decoration-none" style="color:var(--text-primary); background:var(--bg-input); border-radius:var(--radius-md);">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-fill text-brand" style="font-size:1.1rem;"></i>
                    <div>
                        <div style="font-weight:600; font-size:var(--font-size-sm);">Edit Profile</div>
                        <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Update your name, bio, and profile picture</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="/bookmarks" class="d-flex align-items-center justify-content-between p-3 rounded text-decoration-none" style="color:var(--text-primary); background:var(--bg-input); border-radius:var(--radius-md);">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-bookmark-fill text-brand" style="font-size:1.1rem;"></i>
                    <div>
                        <div style="font-weight:600; font-size:var(--font-size-sm);">Saved Posts</div>
                        <div style="font-size:var(--font-size-xs); color:var(--text-muted);">View your bookmarked content</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
    </div>

    <!-- Appearance -->
    <div class="post-card p-3 mb-3">
        <div class="sidebar-card-title">Appearance</div>
        <div class="d-flex align-items-center justify-content-between p-3" style="background:var(--bg-input); border-radius:var(--radius-md);">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-moon-fill text-brand" style="font-size:1.1rem;"></i>
                <div>
                    <div style="font-weight:600; font-size:var(--font-size-sm);">Dark Mode</div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Toggle between light and dark themes</div>
                </div>
            </div>
            <button class="theme-toggle" id="settingsThemeToggle" type="button" aria-label="Toggle theme">
                <i class="bi bi-moon-fill"></i>
            </button>
        </div>
    </div>

    <!-- Notifications -->
    <div class="post-card p-3 mb-3">
        <div class="sidebar-card-title">Notifications</div>
        <div class="d-flex align-items-center justify-content-between p-3" style="background:var(--bg-input); border-radius:var(--radius-md);">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-bell-fill text-brand" style="font-size:1.1rem;"></i>
                <div>
                    <div style="font-weight:600; font-size:var(--font-size-sm);">Push Notifications</div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Enable browser notification popups</div>
                </div>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="notificationToggle" style="width: 2.5em; height: 1.25em; cursor: pointer;">
            </div>
        </div>
    </div>

    <!-- About -->
    <div class="post-card p-3 mb-3">
        <div class="sidebar-card-title">About</div>
        <div class="d-flex flex-column gap-2">
            <a href="/terms" class="d-flex align-items-center justify-content-between p-3 text-decoration-none" style="color:var(--text-primary); background:var(--bg-input); border-radius:var(--radius-md);">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-file-text-fill text-brand" style="font-size:1.1rem;"></i>
                    <span style="font-weight:600; font-size:var(--font-size-sm);">Terms of Service</span>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="/privacy" class="d-flex align-items-center justify-content-between p-3 text-decoration-none" style="color:var(--text-primary); background:var(--bg-input); border-radius:var(--radius-md);">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-shield-fill-check text-brand" style="font-size:1.1rem;"></i>
                    <span style="font-weight:600; font-size:var(--font-size-sm);">Privacy Policy</span>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
    </div>

    <!-- Sign Out -->
    <div class="post-card p-3">
        <a href="/logout" class="d-flex align-items-center gap-3 text-decoration-none p-2">
            <i class="bi bi-box-arrow-right" style="font-size:1.1rem; color:var(--color-danger);"></i>
            <span style="font-weight:600; font-size:var(--font-size-sm); color:var(--color-danger);">Sign Out</span>
        </a>
    </div>

    <div style="text-align:center; margin-top:20px; font-size:var(--font-size-xs); color:var(--text-muted);">
        SamvadHub v2.0 — Built with ❤️ at NIET Greater Noida
    </div>
</div>

<script>
// Sync settings page theme toggle with main toggle
document.addEventListener('DOMContentLoaded', function() {
    var btn  = document.getElementById('settingsThemeToggle');
    var icon = btn ? btn.querySelector('i') : null;
    if (!btn || !icon) return;

    function updateIcon() {
        var theme = document.documentElement.getAttribute('data-theme');
        icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
    updateIcon();

    btn.addEventListener('click', function() {
        var current = document.documentElement.getAttribute('data-theme');
        var next    = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sh-theme', next);
        updateIcon();
    });

    // Notification Toggle handler
    var notifToggle = document.getElementById('notificationToggle');
    if (notifToggle) {
        // Set initial state
        if (typeof isNotificationEnabled === 'function') {
            notifToggle.checked = isNotificationEnabled();
        }

        notifToggle.addEventListener('change', function() {
            if (this.checked) {
                if (typeof requestNotificationPermission === 'function') {
                    requestNotificationPermission(function(granted) {
                        notifToggle.checked = granted;
                    });
                }
            } else {
                localStorage.setItem('samvadhub_notifications_enabled', 'false');
                if (typeof showToast === 'function') {
                    showToast('Notifications disabled.', 'info');
                }
            }
        });
    }
});
</script>
