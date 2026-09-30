<script>
    (() => {
        const storageKey = 'saey-admin-theme';
        const allowedThemes = ['light', 'dark', 'system'];

        try {
            const savedTheme = localStorage.getItem(storageKey);
            const preference = allowedThemes.includes(savedTheme) ? savedTheme : 'system';
            const resolvedTheme = preference === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : preference;

            document.documentElement.dataset.theme = resolvedTheme;
            document.documentElement.dataset.themePreference = preference;
        } catch (error) {
            document.documentElement.dataset.theme = 'dark';
            document.documentElement.dataset.themePreference = 'system';
        }
    })();
</script>
@vite('resources/css/admin.css')
