'use strict';

// ─── Search ───────────────────────────────────────────────────────
function initSearch() {
    const searchInput  = document.getElementById('search');
    const searchResult = document.getElementById('search_result');
    const searchArea   = document.getElementById('sra');
    if (!searchInput || !searchResult) return;

    let searchTimeout = null;

    searchInput.addEventListener('focus', () => {
        searchResult.style.display = 'block';
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResult.contains(e.target)) {
            searchResult.style.display = 'none';
        }
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const keyword = this.value.trim();
        if (keyword.length < 2) {
            searchArea.innerHTML = '<p style="text-align:center; color:var(--text-muted); font-size:var(--font-size-sm); padding:16px;">Type at least 2 characters…</p>';
            return;
        }

        searchArea.innerHTML = '<div style="padding:16px; text-align:center;"><div class="spinner"></div></div>';

        searchTimeout = setTimeout(() => {
            secureAjax(
                '/api/user/search',
                { keyword: keyword },
                function(response) {
                    if (response.status) {
                        searchArea.innerHTML = response.users;
                    } else {
                        searchArea.innerHTML = '<p style="text-align:center; color:var(--text-muted); font-size:var(--font-size-sm); padding:16px;"><i class="bi bi-person-x me-1"></i>No users found.</p>';
                    }
                }
            );
        }, 300); // Debounce 300ms
    });
}
window.initSearch = initSearch;

// Dom Ready bindings
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
});
