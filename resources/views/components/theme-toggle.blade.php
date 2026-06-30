@props([
    'class' => '',
])

<button
    type="button"
    class="ems-theme-toggle relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $class }}"
    aria-label="Toggle dark mode"
    title="Toggle dark mode"
>
    <i class="fas fa-moon text-lg theme-icon-dark hidden" aria-hidden="true"></i>
    <i class="fas fa-sun text-lg theme-icon-light hidden" aria-hidden="true"></i>
</button>

@once
<script>
(function () {
    if (window.__emsThemeToggleInit) return;
    window.__emsThemeToggleInit = true;

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function syncIcons() {
        document.querySelectorAll('.ems-theme-toggle .theme-icon-dark').forEach(function (el) {
            el.classList.toggle('hidden', !isDark());
        });
        document.querySelectorAll('.ems-theme-toggle .theme-icon-light').forEach(function (el) {
            el.classList.toggle('hidden', isDark());
        });
    }

    function setTheme(dark) {
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.dataset.theme = dark ? 'dark' : 'light';
        localStorage.setItem('ems-theme', dark ? 'dark' : 'light');
        syncIcons();
    }

    function toggleTheme() {
        setTheme(!isDark());
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.ems-theme-toggle');
        if (btn) toggleTheme();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncIcons);
    } else {
        syncIcons();
    }
})();
</script>
@endonce
