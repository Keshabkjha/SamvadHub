'use strict';

// ─── Follow Button ────────────────────────────────────────────────
$(document).on('click', '.followbtn', function() {
    const button    = this;
    const user_id   = $(button).data('userId');
    if (!user_id) return;

    $(button).prop('disabled', true).html('<span class="spinner"></span>');

    secureAjax(
        '/api/user/follow',
        { user_id: user_id },
        function(response) {
            if (response.status) {
                $(button)
                    .removeClass('followbtn btn-primary')
                    .addClass('unfollowbtn btn-outline-primary')
                    .data('userId', user_id)
                    .prop('disabled', false)
                    .html('<i class="bi bi-check2"></i> Following');
                showToast('You are now following this user!', 'success');
            } else {
                $(button).prop('disabled', false).html('Follow');
                showToast('Could not follow. Please try again.', 'error');
            }
        }
    );
});

// ─── Unfollow Button ──────────────────────────────────────────────
$(document).on('click', '.unfollowbtn', function() {
    const button  = this;
    const user_id = $(button).data('userId');
    if (!user_id) return;

    $(button).prop('disabled', true).html('<span class="spinner"></span>');

    secureAjax(
        '/api/user/unfollow',
        { user_id: user_id },
        function(response) {
            if (response.status) {
                $(button)
                    .removeClass('unfollowbtn btn-outline-primary')
                    .addClass('followbtn btn-primary')
                    .prop('disabled', false)
                    .html('Follow');
                showToast('Unfollowed.', 'info');
            } else {
                $(button).prop('disabled', false).html('<i class="bi bi-check2"></i> Following');
                showToast('Could not unfollow. Please try again.', 'error');
            }
        }
    );
});

// ─── Unblock User ─────────────────────────────────────────────────
$(document).on('click', '.unblockbtn', function() {
    const button  = this;
    const user_id = $(button).data('userId');
    if (!user_id) return;

    $(button).prop('disabled', true).html('<span class="spinner"></span>');

    secureAjax(
        '/api/user/unblock',
        { user_id: user_id },
        function(response) {
            if (response.status) {
                showToast('User unblocked!', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                $(button).prop('disabled', false).html('Unblock');
                showToast('Could not unblock. Please try again.', 'error');
            }
        }
    );
});

// ─── Report System ────────────────────────────────────────────────
$(document).on('click', '.report-btn', function() {
    const type = $(this).data('type');
    const id   = $(this).data('id');
    document.getElementById('report-type').value = type;
    document.getElementById('report-id').value   = id;

    // Close any open dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(el => el.classList.remove('show'));
    
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    modal.show();
});

$(document).on('click', '.report-reason-btn', function() {
    const reason  = $(this).data('reason');
    const type    = document.getElementById('report-type').value;
    const id      = document.getElementById('report-id').value;

    $(this).prop('disabled', true).html('<span class="spinner"></span>');

    secureAjax(
        '/api/report',
        { type: type, id: id, reason: reason },
        function(response) {
            bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
            if (response.status) {
                showToast('Report submitted. Thank you!', 'success');
            } else {
                showToast('Could not submit report. Please try again.', 'error');
            }
        }
    );
});
