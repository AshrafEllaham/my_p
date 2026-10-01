import DataTable from 'datatables.net-dt';

const shell = document.querySelector('[data-admin-shell]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const themeMenu = document.querySelector('[data-theme-menu]');
const accountMenu = document.querySelector('.admin-account-menu');
const themeOptions = document.querySelectorAll('[data-theme-option]');
const themeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const sidebarMediaQuery = window.matchMedia('(max-width: 860px)');
const themeStorageKey = 'saey-admin-theme';
const sidebarStorageKey = 'saey-admin-sidebar-collapsed';
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

const setSidebarCollapsed = (collapsed, persist = true) => {
    if (!shell || !sidebarToggle) return;

    shell.classList.toggle('is-sidebar-collapsed', collapsed);
    sidebarToggle.setAttribute('aria-expanded', String(!collapsed));

    if (!persist) return;

    try {
        localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
    } catch (error) {
        // The selected sidebar state still applies for the current page.
    }
};

const syncSidebarMode = () => {
    if (!shell || !sidebarToggle) return;

    setSidebar(false);

    if (sidebarMediaQuery.matches) return;

    let collapsed = false;
    try {
        collapsed = localStorage.getItem(sidebarStorageKey) === '1';
    } catch (error) {
        collapsed = false;
    }

    setSidebarCollapsed(collapsed, false);
};

sidebarToggle?.addEventListener('click', () => {
    if (sidebarMediaQuery.matches) {
        setSidebar(!shell?.classList.contains('is-sidebar-open'));
        return;
    }

    setSidebarCollapsed(!shell?.classList.contains('is-sidebar-collapsed'));
});

sidebarClose?.addEventListener('click', () => setSidebar(false));
sidebarMediaQuery.addEventListener('change', syncSidebarMode);
syncSidebarMode();

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

const catalogPage = document.querySelector('[data-catalog-page]');
const catalogTable = document.querySelector('[data-admin-datatable]');
const dataTableConfigNode = document.querySelector('[data-datatable-config]');
let catalogDataTable = null;

if (catalogPage instanceof HTMLElement && catalogTable instanceof HTMLTableElement && dataTableConfigNode) {
    const config = JSON.parse(dataTableConfigNode.textContent || '{}');
    const columns = (config.columns || []).map((column) => {
        const normalized = { ...column };
        delete normalized.title;

        if (normalized.type === 'status') {
            delete normalized.type;
            normalized.render = (value, renderType) => {
                if (renderType !== 'display') return value ? 1 : 0;

                const active = Boolean(value);
                const label = active ? catalogPage.dataset.activeLabel : catalogPage.dataset.inactiveLabel;
                const state = active ? 'active' : 'inactive';

                return `<span class="admin-status-badge admin-status-badge--${state}">${label}</span>`;
            };
        }

        return normalized;
    });

    catalogDataTable = new DataTable(catalogTable, {
        ajax: {
            url: config.ajax,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        },
        columns,
        serverSide: true,
        processing: true,
        responsive: false,
        order: [[0, 'desc']],
        pageLength: 10,
        language: config.language || {},
    });
}

const adminModal = document.querySelector('[data-admin-modal]');
const adminModalTitle = adminModal?.querySelector('[data-modal-title]');
const adminModalContent = adminModal?.querySelector('[data-modal-content]');
const deleteModal = document.querySelector('[data-delete-modal]');
const deleteModalConfirm = deleteModal?.querySelector('[data-delete-confirm]');
const deleteModalError = deleteModal?.querySelector('[data-delete-error]');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let pendingDeleteTrigger = null;

const syncAdminOverlay = () => {
    const hasVisibleModal = [adminModal, deleteModal].some((modal) => modal instanceof HTMLElement && !modal.hidden);
    document.body.classList.toggle('has-admin-overlay', hasVisibleModal);
};

const closeAdminModal = () => {
    if (!(adminModal instanceof HTMLElement)) return;
    adminModal.hidden = true;
    if (adminModalContent instanceof HTMLElement) adminModalContent.replaceChildren();
    syncAdminOverlay();
};

const closeDeleteModal = (restoreFocus = true) => {
    if (!(deleteModal instanceof HTMLElement)) return;

    deleteModal.hidden = true;
    if (deleteModalError instanceof HTMLElement) {
        deleteModalError.hidden = true;
        deleteModalError.textContent = '';
    }
    if (deleteModalConfirm instanceof HTMLButtonElement) {
        deleteModalConfirm.disabled = false;
        deleteModalConfirm.classList.remove('is-loading');
    }

    const trigger = pendingDeleteTrigger;
    pendingDeleteTrigger = null;
    syncAdminOverlay();
    if (restoreFocus && trigger instanceof HTMLElement && trigger.isConnected) trigger.focus();
};

const openDeleteModal = (trigger) => {
    if (!(deleteModal instanceof HTMLElement) || !(trigger instanceof HTMLElement)) return;

    pendingDeleteTrigger = trigger;
    if (deleteModalError instanceof HTMLElement) deleteModalError.hidden = true;
    deleteModal.hidden = false;
    syncAdminOverlay();
    window.requestAnimationFrame(() => deleteModalConfirm?.focus());
};

const showCatalogMessage = (type, message) => {
    if (!(catalogPage instanceof HTMLElement)) return;

    const notice = catalogPage.querySelector(type === 'success' ? '[data-catalog-notice]' : '[data-catalog-error]');
    const counterpart = catalogPage.querySelector(type === 'success' ? '[data-catalog-error]' : '[data-catalog-notice]');
    if (counterpart instanceof HTMLElement) counterpart.hidden = true;
    if (!(notice instanceof HTMLElement)) return;

    notice.textContent = message;
    notice.hidden = false;
    window.setTimeout(() => { notice.hidden = true; }, 4500);
};

const parseJsonResponse = async (response) => {
    try {
        return await response.json();
    } catch (error) {
        return {};
    }
};

const responseErrors = (payload, fallback) => {
    if (payload?.errors && typeof payload.errors === 'object') {
        return Object.values(payload.errors).flat().filter(Boolean);
    }

    return [payload?.message || fallback];
};

const openCatalogModal = async (url, heading) => {
    if (!(adminModal instanceof HTMLElement) || !(adminModalContent instanceof HTMLElement)) return;

    adminModal.hidden = false;
    syncAdminOverlay();
    if (adminModalTitle instanceof HTMLElement) adminModalTitle.textContent = heading || '';
    adminModalContent.innerHTML = '<div class="admin-modal-loading" aria-busy="true"><span></span></div>';

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await parseJsonResponse(response);

        if (!response.ok || !payload?.data?.html) {
            throw new Error(payload?.message || catalogPage?.dataset.genericError);
        }

        adminModalContent.innerHTML = payload.data.html;
        adminModalContent.querySelector('input, select, textarea')?.focus();
    } catch (error) {
        const alert = document.createElement('div');
        alert.className = 'admin-alert admin-alert--danger';
        alert.textContent = error.message || catalogPage?.dataset.genericError;
        adminModalContent.replaceChildren(alert);
    }
};

document.addEventListener('click', async (event) => {
    if (!(event.target instanceof Element)) return;

    const modalTrigger = event.target.closest('[data-modal-url]');
    if (modalTrigger instanceof HTMLElement) {
        event.preventDefault();
        await openCatalogModal(modalTrigger.dataset.modalUrl, modalTrigger.dataset.modalHeading);
        return;
    }

    const modalClose = event.target.closest('[data-modal-close]');
    if (modalClose && modalClose.closest('[data-admin-modal]')) {
        event.preventDefault();
        closeAdminModal();
        return;
    }

    const deleteCancel = event.target.closest('[data-delete-cancel]');
    if (deleteCancel && deleteCancel.closest('[data-delete-modal]')) {
        event.preventDefault();
        closeDeleteModal();
        return;
    }

    const deleteConfirm = event.target.closest('[data-delete-confirm]');
    if (deleteConfirm instanceof HTMLButtonElement) {
        event.preventDefault();
        const deleteTrigger = pendingDeleteTrigger;
        if (!(deleteTrigger instanceof HTMLElement) || !deleteTrigger.dataset.deleteUrl) return;

        deleteTrigger.setAttribute('disabled', 'disabled');
        deleteConfirm.disabled = true;
        deleteConfirm.classList.add('is-loading');

        try {
            const response = await fetch(deleteTrigger.dataset.deleteUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const payload = await parseJsonResponse(response);

            if (!response.ok) {
                throw new Error(responseErrors(payload, catalogPage?.dataset.genericError)[0]);
            }

            closeDeleteModal(false);
            showCatalogMessage('success', payload.message);
            catalogDataTable?.ajax.reload(null, false);
        } catch (error) {
            if (deleteModalError instanceof HTMLElement) {
                deleteModalError.textContent = error.message || catalogPage?.dataset.genericError;
                deleteModalError.hidden = false;
            }
        } finally {
            deleteTrigger.removeAttribute('disabled');
            deleteConfirm.disabled = false;
            deleteConfirm.classList.remove('is-loading');
        }
        return;
    }

    const deleteTrigger = event.target.closest('[data-delete-url]');
    if (!(deleteTrigger instanceof HTMLElement)) return;

    event.preventDefault();
    openDeleteModal(deleteTrigger);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && deleteModal instanceof HTMLElement && !deleteModal.hidden) {
        closeDeleteModal();
    }
});

document.addEventListener('submit', async (event) => {
    if (!(event.target instanceof HTMLFormElement) || !event.target.matches('[data-catalog-form]')) return;

    event.preventDefault();
    const form = event.target;
    const submitButton = form.querySelector('[type="submit"]');
    const errorsBox = form.querySelector('[data-form-errors]');
    if (submitButton instanceof HTMLButtonElement) submitButton.disabled = true;
    if (errorsBox instanceof HTMLElement) errorsBox.hidden = true;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        const payload = await parseJsonResponse(response);

        if (!response.ok) {
            const messages = responseErrors(payload, catalogPage?.dataset.genericError);
            if (errorsBox instanceof HTMLElement) {
                const paragraphs = messages.map((message) => {
                    const paragraph = document.createElement('p');
                    paragraph.textContent = message;
                    return paragraph;
                });
                errorsBox.replaceChildren(...paragraphs);
                errorsBox.hidden = false;
            }
            return;
        }

        closeAdminModal();
        showCatalogMessage('success', payload.message);
        catalogDataTable?.ajax.reload(null, false);
    } catch (error) {
        if (errorsBox instanceof HTMLElement) {
            errorsBox.textContent = error.message || catalogPage?.dataset.genericError;
            errorsBox.hidden = false;
        }
    } finally {
        if (submitButton instanceof HTMLButtonElement) submitButton.disabled = false;
    }
});
