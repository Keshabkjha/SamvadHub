<nav class="navbar navbar-expand-lg">
    <div class="container-fluid" style="max-width:1100px; margin:0 auto; padding:0 16px;">

        <!-- Brand -->
        <a class="navbar-brand" href="/" aria-label="SamvadHub Home">
            <img src="/assets/images/SamvadHub.png" alt="SamvadHub" height="30">
        </a>

        <!-- Search (desktop) -->
        <div class="d-none d-md-block">
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input class="search-input" type="search" id="search" placeholder="Search people…" autocomplete="off" aria-label="Search users">
                <div class="search-results" id="search_result" style="display:none;">
                    <div id="sra" style="padding:8px 0;">
                        <p style="text-align:center; color:var(--text-muted); font-size:var(--font-size-sm); padding:16px;">
                            <i class="bi bi-search me-1"></i>Type to search…
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Nav Actions -->
        <div class="d-none d-lg-flex align-items-center gap-1">

            <!-- Home -->
            <a href="/" class="nav-icon <?= $page_title === 'Home' ? 'active' : '' ?>" title="Home" aria-label="Home">
                <i class="bi bi-house-door-fill"></i>
            </a>

            <!-- Explore -->
            <a href="/explore" class="nav-icon <?= $page_title === 'Explore' ? 'active' : '' ?>" title="Explore" aria-label="Explore">
                <i class="bi bi-compass-fill"></i>
            </a>

            <!-- Add Post -->
            <button class="nav-icon" data-bs-toggle="modal" data-bs-target="#addpost" title="New Post" aria-label="Create new post" type="button">
                <i class="bi bi-plus-square-fill"></i>
            </button>

            <!-- Notifications -->
            <button class="nav-icon" id="show_not" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar" title="Notifications" aria-label="Notifications" type="button" style="position:relative">
                <i class="bi bi-bell-fill"></i>
                <span class="nav-badge" id="notifcounter" style="<?= $notif_count > 0 ? '' : 'display:none;' ?>"><?= $notif_count > 99 ? '99+' : $notif_count ?></span>
            </button>

            <!-- Messages -->
            <button class="nav-icon" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" title="Messages" aria-label="Messages" type="button" style="position:relative">
                <i class="bi bi-chat-dots-fill"></i>
                <span class="nav-badge" id="msgcounter" style="display:none;"></span>
            </button>

            <!-- Bookmarks -->
            <a href="/bookmarks" class="nav-icon <?= $page_title === 'Bookmarks' ? 'active' : '' ?>" title="Bookmarks" aria-label="Bookmarks">
                <i class="bi bi-bookmark-fill"></i>
            </a>

            <!-- Dark Mode Toggle -->
            <button class="nav-icon theme-toggle" id="themeToggle" title="Toggle dark mode" aria-label="Toggle theme" type="button">
                <i class="bi bi-moon-fill"></i>
            </button>

            <!-- Profile Dropdown -->
            <div class="dropdown ms-1">
                <a href="#" class="d-flex align-items-center text-decoration-none" id="profileDropdown"
                   data-bs-toggle="dropdown" aria-expanded="false" aria-label="Profile menu">
                    <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
                         alt="<?= e($user['first_name']) ?>"
                         class="avatar avatar-sm" style="border-color: var(--brand-primary)">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <div style="padding:8px 12px 12px; border-bottom:1px solid var(--border-light);">
                            <div style="font-weight:600; color:var(--text-primary); font-size:var(--font-size-sm);">
                                <?= e($user['first_name']) ?> <?= e($user['last_name']) ?>
                            </div>
                            <div style="font-size:var(--font-size-xs); color:var(--text-muted);">@<?= e($user['username']) ?></div>
                        </div>
                    </li>
                    <li><a class="dropdown-item mt-1" href="/profile/<?= e($user['username']) ?>"><i class="bi bi-person-fill"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="/edit-profile"><i class="bi bi-pencil-fill"></i> Edit Profile</a></li>
                    <li><a class="dropdown-item" href="/bookmarks"><i class="bi bi-bookmark-fill"></i> Bookmarks</a></li>
                    <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear-fill"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Log Out</a></li>
                </ul>
            </div>
        </div>

        <!-- Mobile search icon -->
        <div class="d-flex d-lg-none align-items-center gap-2">
            <button class="nav-icon" data-bs-toggle="offcanvas" data-bs-target="#mobile_search" type="button" aria-label="Search">
                <i class="bi bi-search"></i>
            </button>
            <div class="dropdown">
                <a href="#" data-bs-toggle="dropdown" aria-label="Profile">
                    <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
                         alt="<?= e($user['first_name']) ?>"
                         class="avatar avatar-xs" style="border-color: var(--brand-primary)">
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/profile/<?= e($user['username']) ?>"><i class="bi bi-person-fill"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="/edit-profile"><i class="bi bi-pencil-fill"></i> Edit Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Log Out</a></li>
                </ul>
            </div>
        </div>

    </div>
</nav>

<!-- Mobile search offcanvas -->
<div class="offcanvas offcanvas-top" tabindex="-1" id="mobile_search" style="height:80px">
    <div class="offcanvas-body d-flex align-items-center gap-2 p-3">
        <div class="search-wrapper flex-fill">
            <i class="bi bi-search search-icon"></i>
            <input class="search-input" type="search" id="search_mobile" placeholder="Search people…" autocomplete="off">
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-nav" role="navigation" aria-label="Mobile navigation">
    <a href="/" class="mobile-nav-item <?= $page_title === 'Home' ? 'active' : '' ?>" aria-label="Home">
        <i class="bi bi-house-door-fill"></i>
        <span>Home</span>
    </a>
    <a href="/explore" class="mobile-nav-item <?= $page_title === 'Explore' ? 'active' : '' ?>" aria-label="Explore">
        <i class="bi bi-compass-fill"></i>
        <span>Explore</span>
    </a>
    <button class="mobile-nav-item mobile-nav-post" data-bs-toggle="modal" data-bs-target="#addpost" aria-label="Create post" type="button">
        <i class="bi bi-plus-lg"></i>
    </button>
    <button class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar" aria-label="Notifications" type="button" style="position:relative">
        <i class="bi bi-bell-fill"></i>
        <span>Alerts</span>
        <span class="nav-badge" id="notifcounter_mobile" style="top:-2px; right:8px; <?= $notif_count > 0 ? '' : 'display:none;' ?>"><?= $notif_count > 99 ? '99+' : $notif_count ?></span>
    </button>
    <a href="/profile/<?= e($user['username']) ?>" class="mobile-nav-item" aria-label="Profile">
        <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>" class="avatar" style="width:26px;height:26px;border:none" alt="Profile">
        <span>Profile</span>
    </a>
</nav>