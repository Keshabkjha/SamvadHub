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

// Send message
$(document).on('click', '#sendmsg', function() {
    const user_id = State.chattingUserId;
    const msg     = document.getElementById('msginput').value.trim();
    if (!msg || !user_id) return;

    $('#sendmsg').prop('disabled', true);
    $('#msginput').prop('disabled', true);

    secureAjax(
        '/api/message/send',
        { user_id: user_id, msg: msg },
        function(response) {
            $('#sendmsg').prop('disabled', false);
            $('#msginput').prop('disabled', false).val('').focus();
            if (!response.status) {
                showToast('Could not send message. Please try again.', 'error');
            }
        }
    );
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
    });

    // Start message polling (after a brief delay)
    setTimeout(function() {
        syncMessages();
        startPolling();
    }, 500);
});
