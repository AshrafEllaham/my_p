const shell = document.querySelector('[data-admin-shell]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const themeMenu = document.querySelector('[data-theme-menu]');
const accountMenu = document.querySelector('.admin-account-menu');
const themeOptions = document.querySelectorAll('[data-theme-option]');
const themeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const themeStorageKey = 'saey-admin-theme';
const allowedThemes = ['light', 'dark', 'system'];
let activeThemePreference = allowedThemes.includes(document.documentElement.dataset.themePreference)
    ? document.documentElement.dataset.themePreference
    : 'system';

const applyTheme = (preference, persist = true) => {
    if (!allowedThemes.includes(preference)) return;

    activeThemePreference = preference;
    const resolvedTheme = preference === 'system'
        ? (themeMediaQuery.matches ? 'dark' : 'light')
        : preference;

    document.documentElement.dataset.theme = resolvedTheme;
    document.documentElement.dataset.themePreference = preference;

    themeOptions.forEach((option) => {
        const isActive = option.dataset.themeOption === preference;
        option.classList.toggle('is-active', isActive);
        option.setAttribute('aria-checked', String(isActive));
    });

    if (!persist) return;

    try {
        localStorage.setItem(themeStorageKey, preference);
    } catch (error) {
        // The selected theme still applies for the current page when storage is unavailable.
    }
};

themeOptions.forEach((option) => {
    option.addEventListener('click', () => {
        applyTheme(option.dataset.themeOption);
        if (themeMenu instanceof HTMLDetailsElement) themeMenu.open = false;
    });
});

themeMediaQuery.addEventListener('change', () => {
    if (activeThemePreference === 'system') applyTheme('system', false);
});

applyTheme(activeThemePreference, false);

const updateHeaderMetrics = () => {
    const header = document.querySelector('.admin-header');
    if (!header) return;
    const rect = header.getBoundingClientRect();
    document.documentElement.style.setProperty('--admin-header-bottom', `${Math.round(rect.bottom)}px`);
};

window.addEventListener('resize', updateHeaderMetrics, { passive: true });
window.addEventListener('scroll', updateHeaderMetrics, { passive: true });
document.addEventListener('DOMContentLoaded', updateHeaderMetrics);
updateHeaderMetrics();

const closeAllHeaderMenus = (except = null) => {
    if (themeMenu instanceof HTMLDetailsElement && themeMenu !== except && themeMenu.open) {
        themeMenu.open = false;
    }
    if (accountMenu instanceof HTMLDetailsElement && accountMenu !== except && accountMenu.open) {
        accountMenu.open = false;
    }
};

const setSidebar = (open) => {
    if (!shell || !sidebarToggle) return;

    if (open) {
        closeAllHeaderMenus();
    }

    shell.classList.toggle('is-sidebar-open', open);
    sidebarToggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('has-admin-overlay', open);
    updateHeaderMetrics();
};

sidebarToggle?.addEventListener('click', () => {
    setSidebar(!shell?.classList.contains('is-sidebar-open'));
});

sidebarClose?.addEventListener('click', () => setSidebar(false));

[themeMenu, accountMenu].forEach((menu) => {
    if (!(menu instanceof HTMLDetailsElement)) return;

    menu.querySelector('summary')?.addEventListener('click', () => {
        closeAllHeaderMenus(menu);
        if (shell?.classList.contains('is-sidebar-open')) {
            setSidebar(false);
        }
        updateHeaderMetrics();
    });

    menu.addEventListener('toggle', () => {
        if (menu.open) {
            closeAllHeaderMenus(menu);
            if (shell?.classList.contains('is-sidebar-open')) {
                setSidebar(false);
            }
            updateHeaderMetrics();
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    setSidebar(false);
    const modal = document.querySelector('[data-admin-modal]:not([hidden])');
    if (modal) modal.hidden = true;
    closeAllHeaderMenus();
});

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node)) return;

    if (themeMenu instanceof HTMLDetailsElement && themeMenu.open && !themeMenu.contains(event.target)) {
        themeMenu.open = false;
    }

    if (accountMenu instanceof HTMLDetailsElement && accountMenu.open && !accountMenu.contains(event.target)) {
        accountMenu.open = false;
    }
});

document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('[data-admin-modal]');
        if (modal) modal.hidden = true;
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!(input instanceof HTMLInputElement)) return;

        input.type = input.type === 'password' ? 'text' : 'password';
        button.classList.toggle('is-visible', input.type === 'text');
    });
});

const pageLoader = document.querySelector('[data-page-loader]');
let pageLoaderShowTimer;
let pageLoaderHideTimer;

const showPageLoader = () => {
    if (!(pageLoader instanceof HTMLElement)) return;

    window.clearTimeout(pageLoaderShowTimer);
    window.clearTimeout(pageLoaderHideTimer);

    pageLoaderShowTimer = window.setTimeout(() => {
        pageLoader.hidden = false;
        document.body.classList.add('has-page-loader');
        document.body.setAttribute('aria-busy', 'true');

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => pageLoader.classList.add('is-visible'));
        });
    }, 90);
};

const hidePageLoader = () => {
    if (!(pageLoader instanceof HTMLElement)) return;

    window.clearTimeout(pageLoaderShowTimer);
    window.clearTimeout(pageLoaderHideTimer);
    pageLoader.classList.remove('is-visible');
    document.body.classList.remove('has-page-loader');
    document.body.removeAttribute('aria-busy');

    pageLoaderHideTimer = window.setTimeout(() => {
        pageLoader.hidden = true;
    }, 220);
};

const shouldShowLoaderForLink = (link, event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.hasAttribute('download') || link.target === '_blank' || link.dataset.noLoader !== undefined) return false;

    const rawHref = link.getAttribute('href');
    if (!rawHref || rawHref.startsWith('#') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:') || rawHref.startsWith('javascript:')) return false;

    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin) return false;
    if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash !== window.location.hash) return false;

    return destination.href !== window.location.href;
};

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;

    const link = event.target.closest('a[href]');
    if (!(link instanceof HTMLAnchorElement) || !shouldShowLoaderForLink(link, event)) return;

    showPageLoader();
});

document.addEventListener('submit', (event) => {
    if (!(event.target instanceof HTMLFormElement) || event.target.dataset.noLoader !== undefined) return;
    if (event.target.target === '_blank' || event.target.method === 'dialog') return;

    showPageLoader();
});

window.addEventListener('pageshow', hidePageLoader);
