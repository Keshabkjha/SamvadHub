<div class="auth-page">
    <div class="auth-card verify-card">
        <div style="font-size:3rem; margin-bottom:12px;">📧</div>
        <h1 style="font-size:1.4rem; margin-bottom:6px;">Verify Your Email</h1>
        <p style="color:var(--text-muted); font-size:var(--font-size-sm); margin-bottom:20px;">
            We sent a 6-digit code to<br>
            <strong style="color:var(--text-primary);"><?= e($user['email']) ?></strong>
        </p>

        <?php if (isset($_GET['resent'])): ?>
            <div class="alert alert-success mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                A new verification code has been sent to your email.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['resend_blocked'])): ?>
            <div class="alert alert-warning mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Too many resend attempts. Please wait a few minutes.
            </div>
        <?php endif; ?>

        <?php showError('email_verify'); ?>

        <form method="post" action="/verify-email" novalidate>
            <?= csrf_field() ?>

            <div class="code-inputs" id="codeInputs">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]"
                           id="code<?= $i ?>" tabindex="<?= $i ?>" autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>" aria-label="Digit <?= $i ?>">
                <?php endfor; ?>
                <input type="hidden" name="code" id="codeValue">
            </div>

            <div class="d-flex gap-3 justify-content-center mt-3">
                <button class="btn btn-primary btn-lg" type="submit" id="verifyBtn" style="min-width:160px;">
                    <span class="btn-text">Verify Email</span>
                    <span class="spinner" id="verifySpinner" style="display:none;"></span>
                </button>
            </div>

            <div style="margin-top:20px; text-align:center;">
                <span style="color:var(--text-muted); font-size:var(--font-size-sm);">Didn't receive the code?</span>
                <a href="/resend-verification" class="text-brand ms-1"
                   style="font-size:var(--font-size-sm); font-weight:600; text-decoration:none;">Resend Code</a>
            </div>
        </form>

        <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border-light); text-align:center;">
            <a href="/logout" class="text-brand" style="font-size:var(--font-size-sm); text-decoration:none;">
                <i class="bi bi-arrow-left-circle me-1"></i>Sign out and use a different account
            </a>
        </div>
    </div>
</div>

<script>
// Auto-advance on digit input + collect all digits into hidden field
(function() {
    var inputs = document.querySelectorAll('.code-input');
    var hidden = document.getElementById('codeValue');

    function collectCode() {
        hidden.value = Array.from(inputs).map(i => i.value).join('');
    }

    inputs.forEach(function(input, idx) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(-1);
            collectCode();
            if (this.value && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].value = '';
                collectCode();
            }
        });

        // Handle paste of full code
        input.addEventListener('paste', function(e) {
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            if (pasted.length === 6) {
                e.preventDefault();
                pasted.split('').forEach(function(digit, i) {
                    if (inputs[i]) inputs[i].value = digit;
                });
                collectCode();
                inputs[5].focus();
            }
        });
    });

    inputs[0].focus();

    // Loading state on submit
    document.querySelector('form').addEventListener('submit', function() {
        collectCode();
        if (hidden.value.length !== 6) return;
        document.getElementById('verifyBtn').disabled = true;
        document.querySelector('.btn-text').textContent = 'Verifying…';
        document.getElementById('verifySpinner').style.display = 'inline-block';
    });
})();
</script>