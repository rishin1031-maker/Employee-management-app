{{-- Apply theme before paint to avoid flash; configure Tailwind CDN dark mode --}}
<script>
(function () {
    const stored = localStorage.getItem('ems-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const useDark = stored === 'dark' || (stored === null && prefersDark);
    document.documentElement.classList.toggle('dark', useDark);
    document.documentElement.dataset.theme = useDark ? 'dark' : 'light';
})();
</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
    };
</script>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
