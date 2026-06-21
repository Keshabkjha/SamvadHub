<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/images/SamvadHub.png" alt="SamvadHub Logo">
        </div>

        <?php if (isset($_GET['newuser'])): ?>
            <div class="alert alert-success mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                Account created! Please sign in to continue.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['reseted'])): ?>
            <div class="alert alert-success mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                Password reset successfully. Please sign in.
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error']) && $_SESSION['error']['field'] === 'general'): ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= e($_SESSION['error']['msg']) ?>
            </div>
        <?php endif; ?>

        <h1>Welcome back</h1>
        <p class="auth-subtitle">Sign in to your SamvadHub account</p>

        <form method="post" action="/login" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="username_email">Username or Email</label>
                <input type="text" name="username_email" id="username_email"
                       value="<?= showFormData('username_email') ?>"
                       class="form-control" placeholder="Enter username or email"
                       autocomplete="username" required>
                <?php showError('username_email'); ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_input">Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="password_input"
                           class="form-control" placeholder="Enter your password"
                           autocomplete="current-password" required style="padding-right:44px">
                    <button type="button" id="togglePwd" class="btn-icon"
                           style="position:absolute; right:8px; top:50%; transform:translateY(-50%); color:var(--text-muted); background:none; border:none; cursor:pointer;"
                           aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePwdIcon"></i>
                    </button>
                </div>
                <?php showError('password'); ?>
                <?php showError('checkuser'); ?>
            </div>

            <div style="text-align:right; margin-bottom:20px;">
                <a href="/forgot-password" class="text-brand" style="font-size:var(--font-size-sm); text-decoration:none;">
                    Forgot password?
                </a>
            </div>

            <button class="btn btn-primary btn-full btn-lg" type="submit" id="signInBtn">
                <span class="btn-text">Sign In</span>
                <span class="spinner" id="loginSpinner" style="display:none;"></span>
            </button>

            <div class="divider-text">or</div>

            <!-- Google Sign-in Button -->
            <div class="d-flex justify-content-center mb-3">
                <div id="g_id_onload"
                     data-client_id="112654151448256936934"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-callback="handleCredentialResponse"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="320">
                </div>
            </div>

            <!-- GitHub Sign-in Button -->
            <div class="d-flex justify-content-center mb-3">
                <a href="/auth/github" class="btn btn-dark d-flex align-items-center justify-content-center" style="text-decoration:none; background:#24292e; border-color:#24292e; color:#fff; width:320px; height:40px; font-weight:600; border-radius:4px; font-size:14px;">
                    <i class="bi bi-github me-2" style="font-size:16px;"></i>Sign in with GitHub
                </a>
            </div>

            <a href="/signup" class="btn btn-outline-primary btn-full" style="text-decoration:none; justify-content:center;">
                Create a new account
            </a>
        </form>

        <p style="text-align:center; margin-top:24px; font-size:var(--font-size-xs); color:var(--text-muted);">
            By signing in, you agree to our
            <a href="/terms" style="color:var(--brand-primary);">Terms</a> and
            <a href="/privacy" style="color:var(--brand-primary);">Privacy Policy</a>.
        </p>
    </div>
</div>

<!-- Google Identity Services script -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
// Google OAuth Callback
function handleCredentialResponse(response) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/api/auth/google';
    
    var tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = 'id_token';
    tokenInput.value = response.credential;
    form.appendChild(tokenInput);
    
    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Password toggle
document.getElementById('togglePwd').addEventListener('click', function() {
    var input = document.getElementById('password_input');
    var icon  = document.getElementById('togglePwdIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

// Button loading state on submit
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('signInBtn').disabled = true;
    document.querySelector('.btn-text').textContent = 'Signing in…';
    document.getElementById('loginSpinner').style.display = 'inline-block';
});
</script>
