<?php
// views/pages/privacy.php
$back_url = isset($_SESSION['Auth']) ? '/' : '/login';
$back_text = isset($_SESSION['Auth']) ? 'Back to Home' : 'Back to Login';
?>
<div style="max-width: 800px; margin: 40px auto; padding: 24px 16px;">
    <!-- Navigation back link -->
    <div class="mb-4">
        <a href="<?= $back_url ?>" class="d-inline-flex align-items-center text-decoration-none text-brand" style="font-weight: 600; font-size: var(--font-size-sm);">
            <i class="bi bi-arrow-left me-2"></i><?= $back_text ?>
        </a>
    </div>

    <!-- Content Card -->
    <div class="post-card p-4 p-md-5">
        <h1 class="gradient-heading mb-3" style="font-size: 2rem; font-weight: 800;">
            <i class="bi bi-shield-fill-check me-2 text-brand"></i>Privacy Policy
        </h1>
        <p style="font-size: var(--font-size-xs); color: var(--text-muted); margin-bottom: 30px;">Last updated: June 21, 2026</p>

        <div class="legal-content" style="color: var(--text-secondary); font-size: var(--font-size-sm); line-height: 1.7;">
            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">1. Information We Collect</h3>
                <p>We collect information to provide a better, safer social networking experience. This includes:</p>
                <ul class="ps-3" style="list-style-type: disc;">
                    <li><strong>Account Information:</strong> Your name, username, email address, password, gender, and registration date.</li>
                    <li><strong>Profile Details:</strong> Profile picture, bio, and website link that you choose to supply.</li>
                    <li><strong>User Content:</strong> Posts (text and images), comments, likes, bookmarks, and messages exchanged through chat.</li>
                    <li><strong>Technical Data:</strong> Cookies, IP address, device types, browser types, and session information.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">2. How We Use Information</h3>
                <p>Your data is used to operate and improve SamvadHub, specifically to:</p>
                <ul class="ps-3" style="list-style-type: disc;">
                    <li>Create and manage your account.</li>
                    <li>Publish your posts, comments, likes, and follows.</li>
                    <li>Deliver real-time chat messages and updates.</li>
                    <li>Send verification emails, passwords reset codes, and security notices.</li>
                    <li>Prevent fraudulent behavior, spam, harassment, and abuse.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">3. Data Security</h3>
                <p>We implement security protocols to keep your information secure. Passwords are encrypted using industry-standard <strong>Bcrypt</strong> hashing. However, please remember that no method of transmission over the internet or database storage is 100% secure.</p>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">4. Account Deletion and Retention</h3>
                <p>You have the right to delete your account at any time through the Settings panel. Account deletion is processed as a soft-delete (status marked as deleted), hiding your profile, posts, comments, and interactions from other users. You can contact support if you need permanent database record removal.</p>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">5. Cookies and Local Preference Storage</h3>
                <p>We use cookies and local storage to manage your logged-in session, remember your theme preference (Light/Dark mode), and manage cookie consent preferences. You can decline cookies, but doing so will prevent you from signing in or maintaining a session on our platform.</p>
            </section>

            <section class="mb-0">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">6. Contacting Us</h3>
                <p>If you have any questions about this Privacy Policy, your personal information, or our data handling practices, please contact our data administrator at <a href="mailto:privacy@samvadhub.com" style="color: var(--brand-primary); text-decoration: none; font-weight: 500;">privacy@samvadhub.com</a>.</p>
            </section>
        </div>
    </div>
</div>
