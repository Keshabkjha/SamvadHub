'use strict';

// ─── Chat / Messaging ─────────────────────────────────────────────
function popchat(user_id) {
    State.chattingUserId = user_id;
    State.chatOpen       = true;

    document.getElementById('user_chat').innerHTML = `
        <div style="display:flex; justify-content:center; padding:40px;">
            <div class="spinner"></div>
        </div>`;
    document.getElementById('chatter_username').textContent = 'loading…';
    document.getElementById('chatter_name').textContent     = '';
    document.getElementById('chatter_pic').src = '/assets/images/profile/default_profile.jpg';
    document.getElementById('sendmsg').setAttribute('data-user-id', user_id);
}
window.popchat = popchat;

// Attachment preview helpers
function resetChatAttachment() {
    const fileInput = document.getElementById('chat_img_input');
    if (fileInput) fileInput.value = '';
    const previewContainer = document.getElementById('chat_img_preview_container');
    if (previewContainer) previewContainer.style.display = 'none';
    const previewImg = document.getElementById('chat_img_preview');
    if (previewImg) previewImg.src = '';
}
window.resetChatAttachment = resetChatAttachment;

$(document).on('click', '#attach_img_btn', function() {
    const fileInput = document.getElementById('chat_img_input');
    if (fileInput) fileInput.click();
});

$(document).on('change', '#chat_img_input', function() {
    const file = this.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showToast('File size must be less than 5MB.', 'error');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('chat_img_preview');
            const previewContainer = document.getElementById('chat_img_preview_container');
            if (previewImg && previewContainer) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
});

$(document).on('click', '#chat_img_clear', function() {
    resetChatAttachment();
});

// Send message
$(document).on('click', '#sendmsg', function() {
    const user_id = State.chattingUserId;
    const msg     = document.getElementById('msginput').value.trim();
    const fileInput = document.getElementById('chat_img_input');
    const hasFile = fileInput && fileInput.files && fileInput.files[0];
    
    if (!user_id) return;
    if (!msg && !hasFile) return;

    $('#sendmsg').prop('disabled', true);
    $('#msginput').prop('disabled', true);

    const formData = new FormData();
    formData.append('user_id', user_id);
    formData.append('msg', msg);
    formData.append('csrf_token', getCSRFToken());
    if (hasFile) {
        formData.append('msg_img', fileInput.files[0]);
    }

    $.ajax({
        url:      '/api/message/send',
        method:   'post',
        dataType: 'json',
        data:     formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-Token': getCSRFToken()
        },
        success:  function(response) {
            $('#sendmsg').prop('disabled', false);
            $('#msginput').prop('disabled', false).val('').focus();
            resetChatAttachment();
            if (!response.status) {
                showToast(response.error || 'Could not send message. Please try again.', 'error');
            }
        },
        error: function(xhr) {
            $('#sendmsg').prop('disabled', false);
            $('#msginput').prop('disabled', false).focus();
            if (xhr.status === 401) {
                showToast('Session expired. Please log in again.', 'error');
                setTimeout(() => { window.location.href = '/login'; }, 1500);
            } else {
                showToast('Something went wrong. Please try again.', 'error');
            }
        }
    });
});

// Enter to send
$(document).on('keydown', '#msginput', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        $('#sendmsg').trigger('click');
    }
});

// Sync messages (poll only when sidebar/modal is open)
function syncMessages() {
    $.ajax({
        url:      '/api/message/get',
        method:   'post',
        dataType: 'json',
        data:     { chatter_id: State.chattingUserId },
        success: function(response) {
            // Update chat list
            const chatlist = document.getElementById('chatlist');
            if (chatlist) {
                if (response.chatlist) {
                    chatlist.innerHTML = response.chatlist;
                } else {
                    chatlist.innerHTML = `<div class="empty-state" style="padding:40px 16px;">
                        <i class="bi bi-chat-dots empty-state-icon"></i>
                        <div class="empty-state-title">No conversations yet</div>
                        <div class="empty-state-desc">Start a conversation from someone's profile.</div>
                    </div>`;
                }
            }

            // Update message counter badge
            const msgCounter = document.getElementById('msgcounter');
            if (msgCounter) {
                if (response.newmsgcount > 0) {
                    msgCounter.style.display = 'flex';
                    msgCounter.textContent   = response.newmsgcount > 99 ? '99+' : response.newmsgcount;
                } else {
                    msgCounter.style.display = 'none';
                }
            }

            // Update notification badges (desktop and mobile)
            const notifCounter = document.getElementById('notifcounter');
            const notifCounterMobile = document.getElementById('notifcounter_mobile');
            const unreadNotifCount = parseInt(response.newnotifcount) || 0;
            const unreadMsgCount = parseInt(response.newmsgcount) || 0;

            if (notifCounter) {
                if (unreadNotifCount > 0) {
                    notifCounter.style.display = 'flex';
                    notifCounter.textContent = unreadNotifCount > 99 ? '99+' : unreadNotifCount;
                } else {
                    notifCounter.style.display = 'none';
                }
            }
            if (notifCounterMobile) {
                if (unreadNotifCount > 0) {
                    notifCounterMobile.style.display = 'flex';
                    notifCounterMobile.textContent = unreadNotifCount > 99 ? '99+' : unreadNotifCount;
                } else {
                    notifCounterMobile.style.display = 'none';
                }
            }

            // Check for new notifications/messages and trigger push notifications
            if (typeof isNotificationEnabled === 'function' && isNotificationEnabled()) {
                if (unreadNotifCount > State.lastNotifCount) {
                    new Notification("SamvadHub", {
                        body: "You have new activity on your profile!",
                        icon: "/assets/images/SamvadHub.png"
                    });
                }
                if (unreadMsgCount > State.lastMsgCount && !State.chatOpen) {
                    new Notification("SamvadHub", {
                        body: "You received a new message!",
                        icon: "/assets/images/SamvadHub.png"
                    });
                }
            }

            State.lastNotifCount = unreadNotifCount;
            State.lastMsgCount = unreadMsgCount;

            // Update chat box if open
            if (State.chattingUserId !== 0 && response.chat) {
                const userChat = document.getElementById('user_chat');
                if (userChat) userChat.innerHTML = response.chat.msgs || '';

                // Update header
                if (response.chat.userdata) {
                    const u = response.chat.userdata;
                    const nameEl     = document.getElementById('chatter_name');
                    const usernameEl = document.getElementById('chatter_username');
                    const picEl      = document.getElementById('chatter_pic');
                    const linkEl     = document.getElementById('cplink');

                    if (nameEl)     nameEl.textContent = (u.first_name || '') + ' ' + (u.last_name || '');
                    if (usernameEl) usernameEl.textContent = u.username || '';
                    if (picEl)      picEl.src = '/assets/images/profile/' + (u.profile_pic || 'default_profile.jpg');
                    if (linkEl)     linkEl.href = '/profile/' + encodeURIComponent(u.username || '');
                }

                // Block state
                const sender  = document.getElementById('msgsender');
                const blerror = document.getElementById('blerror');
                if (response.blocked) {
                    if (sender)  sender.style.display  = 'none';
                    if (blerror) blerror.style.display = 'block';
                } else {
                    if (sender)  sender.style.display  = '';
                    if (blerror) blerror.style.display = 'none';
                }
            }

            // Re-init timeago
            jQuery('time.timeago').timeago();
        },
        error: function() {
            // Silent fail on poll
        }
    });
}
window.syncMessages = syncMessages;

// Smart polling — only when page is visible
function startPolling() {
    if (State.pollingInterval) return;
    State.pollingInterval = setInterval(function() {
        if (!document.hidden) syncMessages();
    }, 3000); // Poll every 3s
}
window.startPolling = startPolling;

function stopPolling() {
    if (State.pollingInterval) {
        clearInterval(State.pollingInterval);
        State.pollingInterval = null;
    }
}
window.stopPolling = stopPolling;

// Pause on page hide, resume on show
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopPolling();
    } else {
        syncMessages();
        startPolling();
    }
});

// Clean up on modal close
document.addEventListener('DOMContentLoaded', function() {
    const chatModal = document.getElementById('chatbox');
    if (!chatModal) {
        return; // User is not authenticated, do not start message polling
    }

    chatModal.addEventListener('hidden.bs.modal', function() {
        State.chattingUserId = 0;
        State.chatOpen       = false;
        resetChatAttachment();
    });

    // Start message polling (after a brief delay)
    setTimeout(function() {
        syncMessages();
        startPolling();
    }, 500);
});
