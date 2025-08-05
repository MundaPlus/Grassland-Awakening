<script>
    if (
        localStorage.getItem('color-theme') === 'dark' ||
        (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-bs-theme', 'light');
    }
</script>
