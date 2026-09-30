const shell = document.querySelector('[data-admin-shell]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');

const setSidebar = (open) => {
    if (!shell || !sidebarToggle) return;

    shell.classList.toggle('is-sidebar-open', open);
    sidebarToggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('has-admin-overlay', open);
};

sidebarToggle?.addEventListener('click', () => {
    setSidebar(!shell?.classList.contains('is-sidebar-open'));
});

sidebarClose?.addEventListener('click', () => setSidebar(false));

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    setSidebar(false);
    const modal = document.querySelector('[data-admin-modal]:not([hidden])');
    if (modal) modal.hidden = true;
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
