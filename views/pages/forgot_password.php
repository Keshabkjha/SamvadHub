<div class="auth-page">
    <div class="auth-card" style="max-width:420px;">
        <?php
        if (isset($_SESSION['forgot_code']) && !isset($_SESSION['auth_temp'])) {
            $step = 'verify';
        } elseif (isset($_SESSION['forgot_code']) && isset($_SESSION['auth_temp'])) {
            $step = 'reset';
        } else {
            $step = 'email';
        }
        ?>

        <!-- Step indicator -->
        <div style="display:flex; justify-content:center; gap:8px; margin-bottom:24px;">
            <?php foreach (['email' => '1', 'verify' => '2', 'reset' => '3'] as $s => $n): ?>
                <div style="display:flex; align-items:center; gap:4px;">
                    <div style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700;
                         background: <?= $step === $s || ($s === 'email' && in_array($step, ['verify','reset'])) || ($s === 'verify' && $step === 'reset') ? 'var(--brand-gradient)' : 'var(--bg-input)' ?>;
                         color: <?= $step === $s || ($s === 'email' && in_array($step, ['verify','reset'])) || ($s === 'verify' && $step === 'reset') ? '#fff' : 'var(--text-muted)' ?>;">
                        <?= ($step === 'verify' && $s === 'email') || ($step === 'reset' && in_array($s, ['email','verify'])) ? '✓' : $n ?>
                    </div>
                    <?php if ($n !== '3'): ?>
                        <div style="width:24px; height:2px; background:var(--border-color);"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($step === 'email'): ?>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:2.5rem; margin-bottom:8px;">🔑</div>
                <h1 style="font-size:1.4rem; margin-bottom:4px;">Forgot password?</h1>
                <p style="color:var(--text-muted); font-size:var(--font-size-sm);">
                    Enter your email and we'll send you a reset code.
                </p>
            </div>

            <?php if (isset($_GET['resent'])): ?>
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    If that email is registered, a code has been sent.
                </div>
            <?php endif; ?>

            <?php showError('email'); ?>

            <form method="post" action="/forgot-password" novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="forgot_email">Email Address</label>
                    <input type="email" name="email" id="forgot_email"
                           class="form-control" placeholder="you@example.com"
                           autocomplete="email" required>
                </div>
                <button class="btn btn-primary btn-full btn-lg mt-2" type="submit">
                    Send Reset Code
                </button>
            </form>

        <?php elseif ($step === 'verify'): ?>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:2.5rem; margin-bottom:8px;">📩</div>
                <h1 style="font-size:1.4rem; margin-bottom:4px;">Enter the code</h1>
                <p style="color:var(--text-muted); font-size:var(--font-size-sm);">
                    We sent a 6-digit code to<br>
                    <strong style="color:var(--text-primary);"><?= e($_SESSION['forgot_email'] ?? '') ?></strong>
                </p>
            </div>

            <?php showError('email_verify'); ?>

            <form method="post" action="/verify-forgot-code" novalidate>
                <?= csrf_field() ?>

                <div class="code-inputs" id="codeInputsFP">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric"
                               id="fpc<?= $i ?>" tabindex="<?= $i ?>" autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>">
                    <?php endfor; ?>
                    <input type="hidden" name="code" id="fpCodeValue">
                </div>

                <button class="btn btn-primary btn-full btn-lg mt-3" type="submit">Verify Code</button>
            </form>
            <script>
            (function(){
                var inputs = document.querySelectorAll('#codeInputsFP .code-input');
                var hidden = document.getElementById('fpCodeValue');
                function collect(){ hidden.value = Array.from(inputs).map(i=>i.value).join(''); }
                inputs.forEach(function(input, idx){
                    input.addEventListener('input',function(){
                        this.value=this.value.replace(/[^0-9]/g,'').slice(-1);
                        collect();
                        if(this.value && idx<inputs.length-1) inputs[idx+1].focus();
                    });
                    input.addEventListener('keydown',function(e){
                        if(e.key==='Backspace'&&!this.value&&idx>0){inputs[idx-1].focus();inputs[idx-1].value='';collect();}
                    });
                    input.addEventListener('paste',function(e){
                        var p=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
                        if(p.length===6){e.preventDefault();p.split('').forEach(function(d,i){if(inputs[i])inputs[i].value=d;});collect();inputs[5].focus();}
                    });
                });
                inputs[0].focus();
            })();
            </script>

        <?php elseif ($step === 'reset'): ?>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:2.5rem; margin-bottom:8px;">🛡️</div>
                <h1 style="font-size:1.4rem; margin-bottom:4px;">Set new password</h1>
                <p style="color:var(--text-muted); font-size:var(--font-size-sm);">
                    Create a strong, unique password for your account.
                </p>
            </div>

            <?php showError('password'); ?>

            <form method="post" action="/reset-password" novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="new_password"
                               class="form-control" placeholder="Min. 6 characters"
                               style="padding-right:44px"
                               autocomplete="new-password" required minlength="6">
                        <button type="button" id="toggleNewPwd" class="btn-icon"
                                style="position:absolute; right:8px; top:50%; transform:translateY(-50%); color:var(--text-muted); background:none; border:none; cursor:pointer;">
                            <i class="bi bi-eye" id="toggleNewPwdIcon"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="newPwdBar" style="width:0%;"></div>
                    </div>
                </div>
                <button class="btn btn-primary btn-full btn-lg" type="submit">Set New Password</button>
            </form>
            <script>
            document.getElementById('toggleNewPwd').addEventListener('click',function(){
                var input=document.getElementById('new_password');
                var icon=document.getElementById('toggleNewPwdIcon');
                input.type=input.type==='password'?'text':'password';
                icon.className=input.type==='password'?'bi bi-eye':'bi bi-eye-slash';
            });
            document.getElementById('new_password').addEventListener('input',function(){
                var v=this.value,s=0;
                if(v.length>=6)s++;if(v.length>=10)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
                var colors=['','#EF4444','#F59E0B','#F59E0B','#10B981','#10B981'];
                var bar=document.getElementById('newPwdBar');
                bar.style.width=(s*20)+'%';bar.style.background=colors[s]||'';
            });
            </script>
        <?php endif; ?>

        <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border-light); text-align:center;">
            <a href="/login" class="text-brand" style="font-size:var(--font-size-sm); text-decoration:none;">
                <i class="bi bi-arrow-left-circle me-1"></i>Back to Login
            </a>
        </div>
    </div>
</div>
