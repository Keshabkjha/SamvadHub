<?php
/** @var string $section */
/** @var int $total_users */
/** @var int $total_posts */
/** @var array $reports */
/** @var array $recent_users */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — SamvadHub</title>
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/bootstrap/icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
    <script>(function(){var t=localStorage.getItem('sh-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>

<div style="display:flex; min-height:100vh;">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div style="padding:8px 12px; margin-bottom:16px;">
            <img src="/assets/images/SamvadHub.png" alt="SamvadHub" height="28">
            <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Admin Panel</div>
        </div>

        <nav>
            <a href="/admin?admin=dashboard" class="admin-nav-item <?= $section === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/admin?admin=users" class="admin-nav-item <?= $section === 'users' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Users
                <span class="badge badge-primary ms-auto"><?= $total_users ?></span>
            </a>
            <a href="/admin?admin=posts" class="admin-nav-item <?= $section === 'posts' ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i> Posts
                <span class="badge badge-primary ms-auto"><?= $total_posts ?></span>
            </a>
            <a href="/admin?admin=reports" class="admin-nav-item <?= $section === 'reports' ? 'active' : '' ?>">
                <i class="bi bi-flag-fill"></i> Reports
                <?php if (count($reports) > 0): ?>
                    <span class="badge badge-danger ms-auto"><?= count($reports) ?></span>
                <?php endif; ?>
            </a>
            <hr style="border-color:var(--border-light); margin:8px 0;">
            <a href="/" class="admin-nav-item">
                <i class="bi bi-house-fill"></i> Back to Site
            </a>
            <a href="/logout" class="admin-nav-item" style="color:var(--color-danger);">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main style="flex:1; margin-left:220px; padding:32px; min-height:100vh; background:var(--bg-page);">

        <?php if ($section === 'dashboard'): ?>
            <h1 style="font-size:1.5rem; font-weight:800; margin-bottom:24px;" class="gradient-heading">
                Dashboard
            </h1>

            <!-- Stat Cards -->
            <div class="d-flex gap-4 flex-wrap mb-4">
                <div class="stat-card" style="flex:1; min-width:160px;">
                    <div class="stat-card-number"><?= $total_users ?></div>
                    <div class="stat-card-label"><i class="bi bi-people-fill me-1"></i>Total Users</div>
                </div>
                <div class="stat-card" style="flex:1; min-width:160px;">
                    <div class="stat-card-number"><?= $total_posts ?></div>
                    <div class="stat-card-label"><i class="bi bi-grid-fill me-1"></i>Total Posts</div>
                </div>
                <div class="stat-card" style="flex:1; min-width:160px;">
                    <div class="stat-card-number"><?= count($reports) ?></div>
                    <div class="stat-card-label"><i class="bi bi-flag-fill me-1"></i>Pending Reports</div>
                </div>
            </div>

            <!-- Recent Users Table -->
            <div class="sidebar-card">
                <div class="sidebar-card-title">Recent Registrations</div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:var(--font-size-sm);">
                        <thead>
                            <tr style="border-bottom:2px solid var(--border-color);">
                                <th style="padding:8px 12px; text-align:left; color:var(--text-muted); font-weight:600;">User</th>
                                <th style="padding:8px 12px; text-align:left; color:var(--text-muted); font-weight:600;">Email</th>
                                <th style="padding:8px 12px; text-align:left; color:var(--text-muted); font-weight:600;">Status</th>
                                <th style="padding:8px 12px; text-align:left; color:var(--text-muted); font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $u): ?>
                                <tr style="border-bottom:1px solid var(--border-light);">
                                    <td style="padding:10px 12px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="/assets/images/profile/<?= e($u['profile_pic']) ?>"
                                                 class="avatar avatar-xs" alt="">
                                            <div>
                                                <div style="font-weight:600;"><?= e($u['first_name']) ?> <?= e($u['last_name']) ?></div>
                                                <div style="color:var(--text-muted); font-size:var(--font-size-xs);">@<?= e($u['username']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding:10px 12px; color:var(--text-secondary);"><?= e($u['email']) ?></td>
                                    <td style="padding:10px 12px;">
                                        <?php
                                        $status_map = [
                                            0 => ['Unverified', 'badge-danger'],
                                            1 => ['Active', 'badge-success'],
                                            2 => ['Suspended', 'badge-danger'],
                                            3 => ['Deleted', ''],
                                        ];
                                        $s = $status_map[$u['ac_status']] ?? ['Unknown', ''];
                                        ?>
                                        <span class="badge <?= $s[1] ?>"><?= $s[0] ?></span>
                                        <?php if ($u['is_admin']): ?>
                                            <span class="badge badge-primary ms-1">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <div class="d-flex gap-2">
                                            <a href="/profile/<?= e($u['username']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-person-fill"></i>
                                            </a>
                                            <?php if ($u['ac_status'] == 1): ?>
                                                <a href="/admin/suspend/<?= (int)$u['id'] ?>?from=dashboard"
                                                   onclick="return confirm('Suspend <?= e(addslashes($u['username'])) ?>?')"
                                                   class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:var(--color-danger);">
                                                    <i class="bi bi-ban"></i>
                                                </a>
                                            <?php elseif ($u['ac_status'] == 2): ?>
                                                <a href="/admin/unsuspend/<?= (int)$u['id'] ?>"
                                                   class="btn btn-sm" style="background:rgba(16,185,129,0.1); color:var(--color-success);">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($section === 'reports'): ?>
            <h1 style="font-size:1.5rem; font-weight:800; margin-bottom:24px;" class="gradient-heading">
                <i class="bi bi-flag-fill me-2"></i>Reports
            </h1>

            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="bi bi-shield-check empty-state-icon"></i>
                    <div class="empty-state-title">No pending reports</div>
                    <div class="empty-state-desc">All content reports have been reviewed. Great job!</div>
                </div>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <div class="sidebar-card mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="font-weight:600; margin-bottom:4px;">
                                    <?= $report['post_id'] ? 'Post Report (ID: ' . (int)$report['post_id'] . ')' : 'User Report (ID: ' . (int)$report['reported_user_id'] . ')' ?>
                                </div>
                                <div style="color:var(--text-muted); font-size:var(--font-size-sm);"><?= e($report['reason']) ?></div>
                                <div style="color:var(--text-muted); font-size:var(--font-size-xs); margin-top:4px;"><?= show_time($report['created_at']) ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($report['reported_user_id']): ?>
                                    <a href="/admin/suspend/<?= (int)$report['reported_user_id'] ?>?from=reports"
                                       class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:var(--color-danger);"
                                       onclick="return confirm('Suspend this user?')">
                                        <i class="bi bi-ban me-1"></i>Suspend User
                                    </a>
                                <?php endif; ?>
                                <?php if ($report['post_id']): ?>
                                    <a href="/post/delete/<?= (int)$report['post_id'] ?>"
                                       class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:var(--color-danger);"
                                       onclick="return confirm('Delete this post?')">
                                        <i class="bi bi-trash-fill me-1"></i>Delete Post
                                    </a>
                                <?php endif; ?>
                                <a href="/admin/dismiss-report/<?= (int)$report['id'] ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-x-lg me-1"></i>Dismiss
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    </main>
</div>

<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    var t=localStorage.getItem('sh-theme')||'light';
    document.documentElement.setAttribute('data-theme',t);
})();
</script>
</body>
</html>
