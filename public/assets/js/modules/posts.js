'use strict';

// ─── Post Image Preview ───────────────────────────────────────────
function initPostImagePreview() {
    const input   = document.querySelector('#select_post_img');
    const preview = document.querySelector('#post_img_preview');
    if (!input || !preview) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) { preview.style.display = 'none'; return; }

        // Client-side size check (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToast('Image is too large. Maximum size is 5MB.', 'error');
            this.value = '';
            preview.style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}
window.initPostImagePreview = initPostImagePreview;

// ─── Char Counter for Post Textarea ──────────────────────────────
function initCharCounter() {
    const textarea  = document.getElementById('post_text_input');
    const counter   = document.getElementById('charCount');
    if (!textarea || !counter) return;

    textarea.addEventListener('input', function() {
        const len = this.value.length;
        counter.textContent = len;
        counter.style.color = len > 1900 ? 'var(--color-danger)' : 'var(--text-muted)';
    });
}
window.initCharCounter = initCharCounter;

// ─── Like Button ──────────────────────────────────────────────────
$(document).on('click', '.like_btn', function() {
    const button  = this;
    const post_id = $(button).data('postId');
    if (!post_id) return;

    $(button).prop('disabled', true);

    secureAjax(
        '/api/post/like',
        { post_id: post_id },
        function(response) {
            $(button).prop('disabled', false);
            if (response.status) {
                // Toggle icon and class
                $(button)
                    .removeClass('like_btn')
                    .addClass('unlike_btn liked')
                    .find('i')
                    .removeClass('bi-heart')
                    .addClass('bi-heart-fill heart-animate');

                // Update all like counts for this post
                $(`#likecount${post_id}, #likecount${post_id}m`).each(function() {
                    const text = $(this).text();
                    const num  = parseInt(text) || 0;
                    $(this).text(num + 1);
                });

                // Trigger animation
                setTimeout(() => {
                    $(button).find('i').removeClass('heart-animate');
                }, 500);
            }
        }
    );
});

// ─── Unlike Button ────────────────────────────────────────────────
$(document).on('click', '.unlike_btn', function() {
    const button  = this;
    const post_id = $(button).data('postId');
    if (!post_id) return;

    $(button).prop('disabled', true);

    secureAjax(
        '/api/post/unlike',
        { post_id: post_id },
        function(response) {
            $(button).prop('disabled', false);
            if (response.status) {
                $(button)
                    .removeClass('unlike_btn liked')
                    .addClass('like_btn')
                    .find('i')
                    .removeClass('bi-heart-fill')
                    .addClass('bi-heart');

                $(`#likecount${post_id}, #likecount${post_id}m`).each(function() {
                    const text = $(this).text();
                    const num  = parseInt(text) || 0;
                    $(this).text(Math.max(0, num - 1));
                });
            }
        }
    );
});

// ─── Double-tap to Like (mobile) ──────────────────────────────────
let lastTap = 0;
$(document).on('touchend', '.post-image', function() {
    const now = Date.now();
    if (now - lastTap < 300) {
        const postCard = $(this).closest('.post-card');
        const likeBtn  = postCard.find('.like_btn').first();
        if (likeBtn.length) likeBtn.trigger('click');
    }
    lastTap = now;
});

// ─── Bookmark Button ──────────────────────────────────────────────
$(document).on('click', '.bookmark_btn', function() {
    const button  = this;
    const post_id = $(button).data('postId');
    if (!post_id) return;

    $(button).prop('disabled', true);

    secureAjax(
        '/api/post/bookmark',
        { post_id: post_id },
        function(response) {
            $(button).prop('disabled', false);
            if (response.status) {
                if (response.action === 'added') {
                    $(button).addClass('bookmarked').find('i').removeClass('bi-bookmark').addClass('bi-bookmark-fill');
                    showToast('Post saved to bookmarks!', 'success');
                } else {
                    $(button).removeClass('bookmarked').find('i').removeClass('bi-bookmark-fill').addClass('bi-bookmark');
                    showToast('Removed from bookmarks.', 'info');
                }
            }
        }
    );
});

// ─── Add Comment ──────────────────────────────────────────────────
$(document).on('click', '.add-comment', function() {
    const button  = this;
    const input   = $(button).siblings('.comment-input').length
        ? $(button).siblings('.comment-input')
        : $(button).closest('.comment-input-row, .d-flex').find('.comment-input');
    
    const comment = input.val().trim();
    if (!comment) return;

    const post_id = $(button).data('postId');
    const cs      = $(button).data('cs');
    const page    = $(button).data('page');

    $(button).prop('disabled', true).html('<span class="spinner"></span>');
    input.prop('disabled', true);

    secureAjax(
        '/api/post/comment',
        { post_id: post_id, comment: comment },
        function(response) {
            $(button).prop('disabled', false).html('Post');
            input.prop('disabled', false).val('');

            if (response.status) {
                const section = document.getElementById(cs);
                if (section) {
                    section.insertAdjacentHTML('afterbegin', response.comment);
                    jQuery('time.timeago').timeago();
                }
                showToast('Comment posted!', 'success');
            } else {
                showToast('Could not post comment. Please try again.', 'error');
            }
        }
    );
});

// Allow Enter key to submit comment
$(document).on('keydown', '.comment-input', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        $(this).siblings('.add-comment').length
            ? $(this).siblings('.add-comment').trigger('click')
            : $(this).closest('.comment-input-row, .d-flex').find('.add-comment').trigger('click');
    }
});

// ─── Load More Posts (Infinite Scroll / Button) ───────────────────
const loadMoreBtn = document.getElementById('load-more-btn');
if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
        const offset = parseInt(this.getAttribute('data-offset')) || 20;
        const btn    = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Loading…';

        secureAjax(
            '/api/posts/load',
            { offset: offset },
            function(response) {
                if (response.status && response.html) {
                    document.getElementById('feed-posts').insertAdjacentHTML('beforeend', response.html);
                    btn.setAttribute('data-offset', offset + 10);
                    jQuery('time.timeago').timeago();
                }
                if (!response.has_more) {
                    btn.remove();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load More';
                }
            },
            function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load More';
            }
        );
    });
}

// Dom Ready bindings
document.addEventListener('DOMContentLoaded', function() {
    initPostImagePreview();
    initCharCounter();
});
