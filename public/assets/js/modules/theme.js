'use strict';

// ─── Dark Mode ────────────────────────────────────────────────────
function initTheme() {
    const toggle     = document.getElementById('themeToggle');
    const html       = document.documentElement;
    const sunIcon    = 'bi-sun-fill';
    const moonIcon   = 'bi-moon-fill';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('sh-theme', theme);
        if (toggle) {
            const icon = toggle.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? `bi ${sunIcon}` : `bi ${moonIcon}`;
            }
        }
    }

    // Apply saved theme
    const saved = localStorage.getItem('sh-theme') || 'light';
    applyTheme(saved);

    if (toggle) {
        toggle.addEventListener('click', function() {
            const current = html.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
}

// Make globally accessible
window.initTheme = initTheme;
