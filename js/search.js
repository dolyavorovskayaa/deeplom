document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchToggle = document.getElementById('search-toggle');
    const suggestionsBox = document.getElementById('search-suggestions');
    let timeoutId = null;

    if (searchToggle && window.innerWidth <= 768) {
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.classList.toggle('active');
            if (searchInput.classList.contains('active')) {
                searchInput.focus();
            } else {
                searchInput.value = '';
                suggestionsBox.style.display = 'none';
            }
        });
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                if (!searchInput.value.trim()) {
                    searchInput.classList.remove('active');
                    suggestionsBox.style.display = 'none';
                }
            }, 200);
        });
    }

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value.trim();
        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        timeoutId = setTimeout(() => {
            fetch('search_suggest.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';
                    if (data.length === 0) {
                        suggestionsBox.style.display = 'none';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.innerHTML = `
                            <a href="product.php?id=${item.id}">
                                <span class="suggestion-name">${escapeHtml(item.name)}</span>
                                ${item.brand ? '<span class="suggestion-brand">' + escapeHtml(item.brand) + '</span>' : ''}
                            </a>
                        `;
                        suggestionsBox.appendChild(div);
                    });
                    suggestionsBox.style.display = 'block';
                })
                .catch(err => console.error('Ошибка поиска:', err));
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});