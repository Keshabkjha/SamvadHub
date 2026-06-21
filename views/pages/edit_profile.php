<!-- Cropper.js CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<div style="max-width:720px; margin:0 auto; padding:24px 16px;">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>Profile updated successfully!</span>
        </div>
    <?php endif; ?>

    <h1 class="gradient-heading" style="font-size:1.5rem; margin-bottom:20px;">Edit Profile</h1>

    <div class="post-card p-0" style="overflow:visible;">
        <form method="post" action="/edit-profile"
              enctype="multipart/form-data" id="editProfileForm" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="cropped_image_base64" id="cropped_image_base64">

            <!-- Profile Picture Section -->
            <div style="padding:24px 24px 0; display:flex; align-items:center; gap:20px; border-bottom:1px solid var(--border-light); padding-bottom:20px;">
                <div style="position:relative; display:inline-block;">
                    <img src="/assets/images/profile/<?= e($user['profile_pic']) ?>"
                         alt="Profile picture"
                         id="profile_preview"
                         class="avatar avatar-xxl">
                    <label for="profile_pic"
                           style="position:absolute; bottom:4px; right:4px; width:32px; height:32px;
                                  background:var(--brand-primary); color:white; border-radius:50%;
                                  display:flex; align-items:center; justify-content:center;
                                  cursor:pointer; border:3px solid var(--bg-card); font-size:13px;"
                           title="Change photo" aria-label="Change profile picture">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                    <input type="file" name="profile_pic" id="profile_pic"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           style="display:none;">
                </div>
                <div>
                    <div style="font-size:1rem; font-weight:700; color:var(--text-primary);">
                        <?= e($user['first_name']) ?> <?= e($user['last_name']) ?>
                    </div>
                    <div style="font-size:var(--font-size-sm); color:var(--text-muted);">@<?= e($user['username']) ?></div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:6px;">
                        <i class="bi bi-camera me-1"></i>JPG, PNG, GIF or WebP · Max 5MB
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div style="padding:24px;">
                <div class="d-flex gap-3 mb-3">
                    <div class="flex-fill">
                        <label class="form-label" for="ef_first_name">First Name</label>
                        <input type="text" name="first_name" id="ef_first_name"
                               class="form-control" value="<?= e($user['first_name']) ?>"
                               autocomplete="given-name" required>
                        <?php showError('first_name'); ?>
                    </div>
                    <div class="flex-fill">
                        <label class="form-label" for="ef_last_name">Last Name</label>
                        <input type="text" name="last_name" id="ef_last_name"
                               class="form-control" value="<?= e($user['last_name']) ?>"
                               autocomplete="family-name" required>
                        <?php showError('last_name'); ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ef_username">Username</label>
                    <div style="position:relative">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted);">@</span>
                        <input type="text" name="username" id="ef_username"
                               class="form-control" value="<?= e($user['username']) ?>"
                               style="padding-left:28px"
                               autocomplete="username"
                               pattern="[a-zA-Z0-9_]{3,30}" required>
                    </div>
                    <?php showError('username'); ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ef_bio">Bio</label>
                    <textarea name="bio" id="ef_bio"
                              class="post-textarea"
                              placeholder="Tell people a little about yourself…"
                              rows="3" maxlength="300"><?= e($user['bio'] ?? '') ?></textarea>
                    <div style="text-align:right; font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px;">
                        <span id="bioCount"><?= strlen($user['bio'] ?? '') ?></span>/300
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ef_website">Website</label>
                    <div style="position:relative">
                        <i class="bi bi-link-45deg" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
                        <input type="url" name="website" id="ef_website"
                               class="form-control" value="<?= e($user['website'] ?? '') ?>"
                               placeholder="https://yourwebsite.com"
                               style="padding-left:36px"
                               autocomplete="url">
                    </div>
                    <?php showError('website'); ?>
                </div>

                <!-- Divider -->
                <div class="divider-text">Security</div>

                <div class="mb-3">
                    <label class="form-label" for="ef_password">
                        New Password
                        <span style="font-weight:400; color:var(--text-muted); font-size:var(--font-size-xs);">(leave blank to keep current)</span>
                    </label>
                    <div style="position:relative">
                        <input type="password" name="password" id="ef_password"
                               class="form-control" placeholder="Enter new password…"
                               style="padding-right:44px"
                               autocomplete="new-password">
                        <button type="button" id="toggleEFPwd" class="btn-icon"
                                style="position:absolute; right:8px; top:50%; transform:translateY(-50%); color:var(--text-muted); background:none; border:none; cursor:pointer;">
                            <i class="bi bi-eye" id="toggleEFPwdIcon"></i>
                        </button>
                    </div>
                    <?php showError('profile_pic'); ?>
                    <?php showError('password'); ?>
                </div>

                <!-- Danger Zone -->
                <div class="divider-text" style="color:var(--color-danger);">Danger Zone</div>

                <div style="background:rgba(239,68,68,0.05); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius-md); padding:16px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:var(--font-size-sm); font-weight:600; color:var(--color-danger);">Delete Account</div>
                        <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:2px;">Permanently delete your account and all data. This cannot be undone.</div>
                    </div>
                    <button type="button" class="btn btn-sm"
                            style="background:rgba(239,68,68,0.1); color:var(--color-danger); border:1px solid rgba(239,68,68,0.3); white-space:nowrap;"
                            id="deleteAcctBtn">
                        <i class="bi bi-trash-fill me-1"></i>Delete Account
                    </button>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-3 mt-4">
                    <a href="/" class="btn btn-outline-primary" style="text-decoration:none;">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-fill" id="saveProfileBtn">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteAcctModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:var(--color-danger);">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:var(--text-secondary); font-size:var(--font-size-sm);">
                    This action is <strong>permanent and irreversible</strong>. All your posts, messages, followers, and profile data will be permanently removed.
                </p>
                <p style="color:var(--text-secondary); font-size:var(--font-size-sm); margin-bottom:0;">
                    Type <strong>delete</strong> to confirm:
                </p>
                <input type="text" id="deleteConfirmInput" class="form-control mt-2"
                       placeholder="Type 'delete' to confirm" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="/api/user/delete-account" id="deleteAcctForm">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm" id="confirmDeleteBtn" disabled
                            style="background:var(--color-danger); color:white;">
                        <i class="bi bi-trash-fill me-1"></i>Delete My Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Crop Image Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropModalLabel">
                    <i class="bi bi-crop me-2 text-brand"></i>Crop Profile Picture
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCancelCropHeader"></button>
            </div>
            <div class="modal-body" style="background:var(--bg-body); overflow:hidden; padding:20px;">
                <div style="max-height:400px; width:100%; display:flex; justify-content:center; align-items:center; overflow:hidden;">
                    <img id="crop_image_source" style="max-width:100%; max-height:350px; display:block;" alt="Source to crop">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal" id="btnCancelCrop">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCrop">
                    <i class="bi bi-check-lg me-1"></i>Apply Crop
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Profile pic preview and Cropper.js integration
let cropper = null;
const profilePicInput = document.getElementById('profile_pic');
const cropModalEl = document.getElementById('cropModal');
const cropImageSource = document.getElementById('crop_image_source');
const croppedImageBase64 = document.getElementById('cropped_image_base64');
const profilePreview = document.getElementById('profile_preview');
const btnSaveCrop = document.getElementById('btnSaveCrop');
const btnCancelCrop = document.getElementById('btnCancelCrop');
const btnCancelCropHeader = document.getElementById('btnCancelCropHeader');

let cropModal = null;

profilePicInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG, PNG, GIF, and WebP images are allowed.');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            cropImageSource.src = e.target.result;
            if (!cropModal) {
                cropModal = new bootstrap.Modal(cropModalEl);
            }
            cropModal.show();
        };
        reader.readAsDataURL(file);
    }
});

cropModalEl.addEventListener('shown.bs.modal', function() {
    cropper = new Cropper(cropImageSource, {
        aspectRatio: 1,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
});

cropModalEl.addEventListener('hidden.bs.modal', function() {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

btnSaveCrop.addEventListener('click', function() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        if (canvas) {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            croppedImageBase64.value = dataUrl;
            profilePreview.src = dataUrl;
            cropModal.hide();
        }
    }
});

const cancelHandler = function() {
    profilePicInput.value = '';
};
btnCancelCrop.addEventListener('click', cancelHandler);
btnCancelCropHeader.addEventListener('click', cancelHandler);

// Bio char counter
document.getElementById('ef_bio').addEventListener('input', function() {
    document.getElementById('bioCount').textContent = this.value.length;
});

// Password toggle
document.getElementById('toggleEFPwd').addEventListener('click', function() {
    var input = document.getElementById('ef_password');
    var icon  = document.getElementById('toggleEFPwdIcon');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Delete account modal
document.getElementById('deleteAcctBtn').addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('deleteAcctModal')).show();
});

document.getElementById('deleteConfirmInput').addEventListener('input', function() {
    document.getElementById('confirmDeleteBtn').disabled = this.value.toLowerCase() !== 'delete';
});
</script>
