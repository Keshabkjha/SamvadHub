<?php http_response_code(404); ?>
<div style="min-height:70vh; display:flex; align-items:center; justify-content:center; padding:40px 16px;">
    <div style="text-align:center; max-width:400px;">
        <div style="font-size:6rem; font-weight:800; background:var(--brand-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; line-height:1;">404</div>
        <h1 style="font-size:1.5rem; font-weight:700; margin:16px 0 8px;">Page Not Found</h1>
        <p style="color:var(--text-muted); font-size:var(--font-size-sm); margin-bottom:24px;">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="?" class="btn btn-primary text-decoration-none">
                <i class="bi bi-house-fill me-1"></i>Go Home
            </a>
            <button onclick="history.back()" class="btn btn-outline-primary" type="button">
                <i class="bi bi-arrow-left me-1"></i>Go Back
            </button>
        </div>
    </div>
</div>
