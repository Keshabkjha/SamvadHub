<div class="auth-page">
    <div class="auth-card" style="max-width:500px;">
        <div class="auth-logo">
            <img src="/assets/images/SamvadHub.png" alt="SamvadHub Logo">
        </div>

        <h1>Create account</h1>
        <p class="auth-subtitle">Join SamvadHub and start connecting</p>

        <form method="post" action="/signup" novalidate>
            <?= csrf_field() ?>

            <div class="d-flex gap-3">
                <div class="form-group flex-fill">
                    <label class="form-label" for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name"
                           value="<?= showFormData('first_name') ?>"
                           class="form-control" placeholder="First name"
                           autocomplete="given-name" required>
                    <?php showError('first_name'); ?>
                </div>
                <div class="form-group flex-fill">
                    <label class="form-label" for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name"
                           value="<?= showFormData('last_name') ?>"
                           class="form-control" placeholder="Last name"
                           autocomplete="family-name" required>
                    <?php showError('last_name'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Gender</label>
                <div class="d-flex gap-3">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:var(--font-size-sm)">
                        <input class="form-check-input" type="radio" name="gender" value="1"
                               <?= (showFormData('gender') === '' || showFormData('gender') === '1') ? 'checked' : '' ?>>
                        Male
                    </label>
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:var(--font-size-sm)">
                        <input class="form-check-input" type="radio" name="gender" value="2"
                               <?= showFormData('gender') === '2' ? 'checked' : '' ?>>
                        Female
                    </label>
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:var(--font-size-sm)">
                        <input class="form-check-input" type="radio" name="gender" value="0"
                               <?= showFormData('gender') === '0' ? 'checked' : '' ?>>
                        Other
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" name="email" id="email"
                       value="<?= showFormData('email') ?>"
                       class="form-control" placeholder="you@example.com"
                       autocomplete="email" required>
                <?php showError('email'); ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div style="position:relative">
                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:var(--font-size-sm);">@</span>
                    <input type="text" name="username" id="username"
                           value="<?= showFormData('username') ?>"
                           class="form-control" placeholder="your_username"
                           style="padding-left:28px"
                           autocomplete="username"
                           pattern="[a-zA-Z0-9_]{3,30}"
                           title="3–30 characters: letters, numbers, underscores" required>
                </div>
                <?php showError('username'); ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="signup_password">Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="signup_password"
                           class="form-control" placeholder="Min. 6 characters"
                           style="padding-right:44px"
                           autocomplete="new-password" required minlength="6">
                    <button type="button" id="togglePwd2" class="btn-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); color:var(--text-muted); background:none; border:none; cursor:pointer;"
                            aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePwdIcon2"></i>
                    </button>
                </div>
                <!-- Password strength bar -->
                <div class="password-strength">
                    <div class="password-strength-bar" id="pwdStrengthBar" style="width:0%; background:var(--color-danger);"></div>
                </div>
                <div style="font-size:10px; color:var(--text-muted); margin-top:4px;" id="pwdStrengthLabel"></div>
                <?php showError('password'); ?>
            </div>

            <button class="btn btn-primary btn-full btn-lg mt-2" type="submit" id="signUpBtn">
                <span class="btn-text">Create Account</span>
                <span class="spinner" id="signupSpinner" style="display:none;"></span>
            </button>

            <div class="divider-text">already have an account?</div>

            <a href="/login" class="btn btn-outline-primary btn-full" style="text-decoration:none; justify-content:center;">
                Sign In Instead
            </a>
        </form>

        <p style="text-align:center; margin-top:20px; font-size:var(--font-size-xs); color:var(--text-muted);">
            By creating an account, you agree to our
            <a href="/terms" style="color:var(--brand-primary);">Terms</a> and
            <a href="/privacy" style="color:var(--brand-primary);">Privacy Policy</a>.
        </p>
    </div>
</div>

<script>
// Password toggle
document.getElementById('togglePwd2').addEventListener('click', function() {
    var input = document.getElementById('signup_password');
    var icon  = document.getElementById('togglePwdIcon2');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Password strength
document.getElementById('signup_password').addEventListener('input', function() {
    var val = this.value;
    var strength = 0;
    if (val.length >= 6)  strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    var bar   = document.getElementById('pwdStrengthBar');
    var label = document.getElementById('pwdStrengthLabel');
    var colors = ['', '#EF4444', '#F59E0B', '#F59E0B', '#10B981', '#10B981'];
    var labels = ['', 'Weak', 'Fair', 'Fair', 'Strong', 'Very Strong'];
    bar.style.width     = (strength * 20) + '%';
    bar.style.background = colors[strength] || '';
    label.textContent   = val.length > 0 ? labels[strength] : '';
    label.style.color   = colors[strength];
});

// Loading state
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('signUpBtn').disabled = true;
    document.querySelector('.btn-text').textContent = 'Creating account…';
    document.getElementById('signupSpinner').style.display = 'inline-block';
});
</script>
