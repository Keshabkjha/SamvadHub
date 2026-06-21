'use strict';

// ─── Global State ─────────────────────────────────────────────────
const State = {
    chattingUserId: 0,
    pollingInterval: null,
    pollingActive: false,
    chatOpen: false,
    lastNotifCount: 0,
    lastMsgCount: 0,
};
window.State = State;

// Initialize counts on page load
$(document).ready(function() {
    const notifEl = document.getElementById('notifcounter');
    const msgEl = document.getElementById('msgcounter');
    State.lastNotifCount = notifEl && notifEl.style.display !== 'none' ? parseInt(notifEl.textContent) || 0 : 0;
    State.lastMsgCount = msgEl && msgEl.style.display !== 'none' ? parseInt(msgEl.textContent) || 0 : 0;
});

// ─── Notification Permission Helpers ──────────────────────────────
function isNotificationEnabled() {
    return localStorage.getItem('samvadhub_notifications_enabled') === 'true' && Notification.permission === 'granted';
}
window.isNotificationEnabled = isNotificationEnabled;

function requestNotificationPermission(callback) {
    if (!('Notification' in window)) {
        showToast('Browser does not support notifications.', 'error');
        if (typeof callback === 'function') callback(false);
        return;
    }
    Notification.requestPermission().then(permission => {
        const enabled = (permission === 'granted');
        localStorage.setItem('samvadhub_notifications_enabled', enabled ? 'true' : 'false');
        if (enabled) {
            showToast('Browser notifications enabled!', 'success');
        } else {
            showToast('Notifications permission denied.', 'warning');
        }
        if (typeof callback === 'function') callback(enabled);
    });
}
window.requestNotificationPermission = requestNotificationPermission;

// ─── Toast Notification System ────────────────────────────────────
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: 'bi-check-circle-fill',
        error:   'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill',
    };

    const toast = document.createElement('div');
    toast.className = `toast-msg ${type}`;
    toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 350);
    }, duration);
}
window.showToast = showToast;

// ─── CSRF Token helper ────────────────────────────────────────────
function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}
window.getCSRFToken = getCSRFToken;

// ─── Secure AJAX wrapper ──────────────────────────────────────────
function secureAjax(url, data, onSuccess, onError) {
    $.ajax({
        url:      url,
        method:   'post',
        dataType: 'json',
        data:     data,
        success:  onSuccess,
        error: function(xhr) {
            if (xhr.status === 401) {
                showToast('Session expired. Please log in again.', 'error');
                setTimeout(() => { window.location.href = '/login'; }, 1500);
            } else if (xhr.status === 403) {
                showToast('Invalid request. Please refresh the page.', 'error');
            } else {
                if (typeof onError === 'function') onError(xhr);
                else showToast('Something went wrong. Please try again.', 'error');
            }
        }
    });
}
window.secureAjax = secureAjax;

// ─── Mark Notifications Read ──────────────────────────────────────
const notifSidebar = document.getElementById('notification_sidebar');
if (notifSidebar) {
    notifSidebar.addEventListener('show.bs.offcanvas', function() {
        secureAjax('/api/notification/read', {}, function(r) {
            if (r.status) {
                const badge = document.getElementById('notifcounter');
                if (badge) badge.style.display = 'none';
                const badgeMobile = document.getElementById('notifcounter_mobile');
                if (badgeMobile) badgeMobile.style.display = 'none';
                if (window.State) {
                    window.State.lastNotifCount = 0;
                }
            }
        });
    });
}

// ─── Timeago Init ─────────────────────────────────────────────────
jQuery(document).ready(function() {
    jQuery('time.timeago').timeago();
});

// ─── Document Bootstrap bindings ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    // Lazy load images using IntersectionObserver
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '100px' });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            observer.observe(img);
        });
    }

    // Prevent double form submission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            setTimeout(() => {
                this.querySelectorAll('[type="submit"]').forEach(btn => {
                    btn.disabled = true;
                });
            }, 100);
        });
    });
});
