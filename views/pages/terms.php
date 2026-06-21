<?php
// views/pages/terms.php
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
            <i class="bi bi-file-text-fill me-2 text-brand"></i>Terms of Service
        </h1>
        <p style="font-size: var(--font-size-xs); color: var(--text-muted); margin-bottom: 30px;">Last updated: June 21, 2026</p>

        <div class="legal-content" style="color: var(--text-secondary); font-size: var(--font-size-sm); line-height: 1.7;">
            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">1. Acceptance of Terms</h3>
                <p>Welcome to SamvadHub ("we," "our," "us"). By accessing or using our platform, website, and services, you agree to comply with and be bound by these Terms of Service. If you do not agree to these terms, please do not use SamvadHub.</p>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">2. Account Registration and Security</h3>
                <p>To access certain features of the platform, you must register for an account. You agree to:</p>
                <ul class="ps-3" style="list-style-type: disc;">
                    <li>Provide accurate, current, and complete information during registration.</li>
                    <li>Maintain the security and confidentiality of your account credentials.</li>
                    <li>Promptly update your information to keep it accurate.</li>
                    <li>Notify us immediately of any unauthorized use or security breach of your account.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">3. User Conduct & Content Rules</h3>
                <p>You are solely responsible for the content you post, publish, or share on SamvadHub. You agree not to upload, post, or transmit any content that:</p>
                <ul class="ps-3" style="list-style-type: disc;">
                    <li>Is illegal, defamatory, abusive, harassing, threatening, or hateful.</li>
                    <li>Violates copyright, trademark, privacy, or proprietary rights of others.</li>
                    <li>Contains software viruses, malware, or any code designed to disrupt the services.</li>
                    <li>Constitutes spam, unauthorized advertising, or deceptive promotional content.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">4. Moderation, Reporting, and Account Suspension</h3>
                <p>We reserve the right, but do not assume the obligation, to monitor and moderate content. Users can report posts or accounts that violate our guidelines. We reserve the right to suspend, terminate, or disable accounts (soft-delete) at our discretion for violations of these Terms of Service.</p>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">5. Limitation of Liability</h3>
                <p>To the maximum extent permitted by law, SamvadHub and its creators shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, resulting from your use of the platform.</p>
            </section>

            <section class="mb-4">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">6. Modifications to Terms</h3>
                <p>We reserve the right to modify these Terms of Service at any time. We will notify you of any changes by updating the "Last updated" date at the top of this page. Your continued use of the platform after changes are posted constitutes acceptance of those changes.</p>
            </section>

            <section class="mb-0">
                <h3 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">7. Contact Us</h3>
                <p>If you have any questions or concerns about these Terms of Service, please contact us at <a href="mailto:support@samvadhub.com" style="color: var(--brand-primary); text-decoration: none; font-weight: 500;">support@samvadhub.com</a>.</p>
            </section>
        </div>
    </div>
</div>
