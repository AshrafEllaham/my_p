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
    if (adminModal instanceof HTMLElement && !adminModal.hidden) {
        closeAdminModal();
    }
    if (deleteModal instanceof HTMLElement && !deleteModal.hidden) {
        closeDeleteModal();
    }
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
            data: (d) => {
                const filterControls = catalogPage.querySelectorAll('[data-filter-control]');
                filterControls.forEach((control) => {
                    const name = control.getAttribute('name');
                    if (!name) return;

                    if (control instanceof HTMLInputElement && control.type === 'radio') {
                        if (control.checked && control.value !== '') {
                            d[name] = control.value;
                        }
                    } else if (control instanceof HTMLSelectElement || control instanceof HTMLInputElement) {
                        if (control.value !== '') {
                            d[name] = control.value;
                        }
                    }
                });
            },
        },
        columns,
        serverSide: true,
        processing: true,
        responsive: false,
        order: [[0, 'desc']],
        pageLength: 10,
        language: config.language || {},
    });

    const filterControls = catalogPage.querySelectorAll('[data-filter-control]');
    filterControls.forEach((control) => {
        control.addEventListener('change', () => {
            if (control instanceof HTMLInputElement && control.type === 'radio') {
                const group = catalogPage.querySelectorAll(`input[type="radio"][name="${control.name}"]`);
                group.forEach((radio) => {
                    const tab = radio.closest('.admin-filter-tab');
                    if (tab) {
                        tab.classList.toggle('is-active', radio.checked);
                    }
                });
            }

            const url = new URL(window.location.href);
            if (control.value !== '') {
                url.searchParams.set(control.name, control.value);
            } else {
                url.searchParams.delete(control.name);
            }
            window.history.replaceState({}, '', url.toString());

            catalogDataTable?.ajax.reload();
        });
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

let adminModalCloseTimer = null;
let deleteModalCloseTimer = null;

const syncAdminOverlay = () => {
    const hasVisibleModal = [adminModal, deleteModal].some((modal) => modal instanceof HTMLElement && !modal.hidden);
    document.body.classList.toggle('has-admin-overlay', hasVisibleModal);
};

const closeAdminModal = (immediate = false) => {
    if (!(adminModal instanceof HTMLElement)) return;

    window.clearTimeout(adminModalCloseTimer);
    adminModal.classList.remove('is-open');

    const dialog = adminModal.querySelector('.admin-modal__dialog');
    const backdrop = adminModal.querySelector('.admin-modal__backdrop');
    if (dialog instanceof HTMLElement) {
        dialog.style.transform = '';
        dialog.style.transition = '';
    }
    if (backdrop instanceof HTMLElement) {
        backdrop.style.opacity = '';
        backdrop.style.transition = '';
    }

    const finalize = () => {
        adminModal.hidden = true;
        if (adminModalContent instanceof HTMLElement) adminModalContent.replaceChildren();
        syncAdminOverlay();
    };

    if (immediate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        finalize();
    } else {
        adminModalCloseTimer = window.setTimeout(finalize, 280);
    }
};

const activateBottomSheet = (modal) => {
    if (!(modal instanceof HTMLElement)) return;

    modal.hidden = false;
    const dialog = modal.querySelector('.admin-modal__dialog');
    const backdrop = modal.querySelector('.admin-modal__backdrop');
    if (dialog instanceof HTMLElement) {
        dialog.style.transform = '';
        dialog.style.transition = '';
    }
    if (backdrop instanceof HTMLElement) {
        backdrop.style.opacity = '';
        backdrop.style.transition = '';
    }

    syncAdminOverlay();
    // Force a synchronous reflow to start transition immediately on current frame
    void modal.offsetHeight;
    modal.classList.add('is-open');
};

const openAdminModal = () => {
    if (!(adminModal instanceof HTMLElement)) return;
    window.clearTimeout(adminModalCloseTimer);
    activateBottomSheet(adminModal);
};

const closeDeleteModal = (restoreFocus = true, immediate = false) => {
    if (!(deleteModal instanceof HTMLElement)) return;

    window.clearTimeout(deleteModalCloseTimer);
    deleteModal.classList.remove('is-open');

    const dialog = deleteModal.querySelector('.admin-modal__dialog');
    const backdrop = deleteModal.querySelector('.admin-modal__backdrop');
    if (dialog instanceof HTMLElement) {
        dialog.style.transform = '';
        dialog.style.transition = '';
    }
    if (backdrop instanceof HTMLElement) {
        backdrop.style.opacity = '';
        backdrop.style.transition = '';
    }

    const trigger = pendingDeleteTrigger;
    pendingDeleteTrigger = null;

    const finalize = () => {
        deleteModal.hidden = true;
        if (deleteModalError instanceof HTMLElement) {
            deleteModalError.hidden = true;
            deleteModalError.textContent = '';
        }
        if (deleteModalConfirm instanceof HTMLButtonElement) {
            deleteModalConfirm.disabled = false;
            deleteModalConfirm.classList.remove('is-loading');
        }
        syncAdminOverlay();
        if (restoreFocus && trigger instanceof HTMLElement && trigger.isConnected) trigger.focus();
    };

    if (immediate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        finalize();
    } else {
        deleteModalCloseTimer = window.setTimeout(finalize, 280);
    }
};

const openDeleteModal = (trigger) => {
    if (!(deleteModal instanceof HTMLElement) || !(trigger instanceof HTMLElement)) return;

    window.clearTimeout(deleteModalCloseTimer);
    pendingDeleteTrigger = trigger;
    if (deleteModalError instanceof HTMLElement) deleteModalError.hidden = true;

    activateBottomSheet(deleteModal);

    window.setTimeout(() => {
        if (!deleteModal.hidden && deleteModal.classList.contains('is-open')) {
            deleteModalConfirm?.focus({ preventScroll: true });
        }
    }, 280);
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

const initializeImageDropify = (container = document) => {
    const jquery = window.jQuery;
    if (typeof jquery !== 'function' || typeof jquery.fn?.dropify !== 'function') return;

    const root = container || document;
    const inputs = root instanceof HTMLInputElement && root.matches('input.dropify')
        ? [root]
        : Array.from(root.querySelectorAll('input.dropify'));

    inputs.forEach((input) => {
        if (!(input instanceof HTMLInputElement) || jquery(input).data('dropify')) return;

        const $input = jquery(input);
        const maxFileSize = input.dataset.maxFileSize || '5M';
        const height = input.dataset.height ? parseInt(input.dataset.height, 10) : null;

        $input.dropify({
            maxFileSize: maxFileSize,
            allowedFileExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            messages: {
                default: input.dataset.dropifyDefault || '',
                replace: input.dataset.dropifyReplace || '',
                remove: input.dataset.dropifyRemove || '',
                error: input.dataset.dropifyError || '',
            },
            tpl: {
                message: '<div class="dropify-message"><span class="dropify-upload-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></span><p>{{ default }}</p></div>',
                clearButton: `<button type="button" class="dropify-clear" aria-label="${input.dataset.dropifyRemove || 'Remove'}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg></button>`,
            },
            error: {
                fileSize: input.dataset.dropifyFileSizeError || '',
                fileExtension: input.dataset.dropifyFileTypeError || '',
            },
            ...(height ? { height } : {}),
        });

        const wrapper = $input.closest('.dropify-wrapper');
        if (input.classList.contains('has-error')) {
            wrapper.addClass('has-error');
        }

        $input.on('dropify.fileReady', () => {
            wrapper.removeClass('has-error');
        });

        $input.on('dropify.afterClear', (event, instance) => {
            const defaultFile = instance.settings.defaultFile;
            if (!defaultFile) return;

            instance.file.name = instance.cleanFilename(defaultFile);
            instance.setPreview(instance.isImage(), defaultFile);
        });
    });
};

window.initializeImageDropify = initializeImageDropify;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initializeImageDropify(document));
} else {
    initializeImageDropify(document);
}
window.addEventListener('load', () => initializeImageDropify(document));

const openCatalogModal = async (url, heading) => {
    if (!(adminModal instanceof HTMLElement) || !(adminModalContent instanceof HTMLElement)) return;

    openAdminModal();
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
        initializeImageDropify(adminModalContent);
        adminModalContent.querySelector('input:not(.dropify), select, textarea')?.focus();
    } catch (error) {
        const alert = document.createElement('div');
        alert.className = 'admin-alert admin-alert--danger';
        alert.textContent = error.message || catalogPage?.dataset.genericError;
        adminModalContent.replaceChildren(alert);
    }
};

const initBottomSheetGestures = () => {
    if (!(adminModal instanceof HTMLElement)) return;

    const dialog = adminModal.querySelector('.admin-modal__dialog');
    const backdrop = adminModal.querySelector('.admin-modal__backdrop');
    const dragHandle = adminModal.querySelector('[data-sheet-drag-handle]');
    const header = adminModal.querySelector('.admin-modal__header');
    if (!(dialog instanceof HTMLElement)) return;

    let startY = 0;
    let currentDeltaY = 0;
    let isDragging = false;
    let startTime = 0;

    const onPointerDown = (event) => {
        if (!event.isPrimary) return;
        if (event.target instanceof Element && event.target.closest('button, a, input, select, textarea')) {
            return;
        }

        isDragging = true;
        startY = event.clientY;
        currentDeltaY = 0;
        startTime = performance.now();

        try {
            event.target.setPointerCapture?.(event.pointerId);
        } catch (_) {}
    };

    const onPointerMove = (event) => {
        if (!isDragging) return;

        const dy = event.clientY - startY;
        if (dy > 0) {
            currentDeltaY = dy;
            dialog.style.transform = `translateY(${dy}px)`;
            dialog.style.transition = 'none';
            if (backdrop instanceof HTMLElement) {
                const opacity = Math.max(0.12, 1 - (dy / 380));
                backdrop.style.opacity = String(opacity);
                backdrop.style.transition = 'none';
            }
        } else {
            currentDeltaY = 0;
            const resistance = dy * 0.18;
            dialog.style.transform = `translateY(${resistance}px)`;
            dialog.style.transition = 'none';
        }
    };

    const onPointerUp = (event) => {
        if (!isDragging) return;
        isDragging = false;

        try {
            event.target.releasePointerCapture?.(event.pointerId);
        } catch (_) {}

        const elapsed = performance.now() - startTime;
        const velocity = currentDeltaY / Math.max(elapsed, 1);

        if (currentDeltaY > 110 || (currentDeltaY > 40 && velocity > 0.45)) {
            dialog.style.transition = 'transform 220ms cubic-bezier(0.16, 1, 0.3, 1)';
            dialog.style.transform = 'translateY(100%)';
            if (backdrop instanceof HTMLElement) {
                backdrop.style.transition = 'opacity 220ms cubic-bezier(0.16, 1, 0.3, 1)';
                backdrop.style.opacity = '0';
            }
            window.setTimeout(() => {
                closeAdminModal(true);
            }, 220);
        } else {
            dialog.style.transition = 'transform 260ms cubic-bezier(0.16, 1, 0.3, 1)';
            dialog.style.transform = 'translateY(0)';
            if (backdrop instanceof HTMLElement) {
                backdrop.style.transition = 'opacity 260ms cubic-bezier(0.16, 1, 0.3, 1)';
                backdrop.style.opacity = '1';
            }
            window.setTimeout(() => {
                dialog.style.transition = '';
                dialog.style.transform = '';
                if (backdrop instanceof HTMLElement) {
                    backdrop.style.transition = '';
                    backdrop.style.opacity = '';
                }
            }, 260);
        }
    };

    if (dragHandle instanceof HTMLElement) {
        dragHandle.addEventListener('pointerdown', onPointerDown);
        dragHandle.addEventListener('pointermove', onPointerMove);
        dragHandle.addEventListener('pointerup', onPointerUp);
        dragHandle.addEventListener('pointercancel', onPointerUp);
    }

    if (header instanceof HTMLElement) {
        header.addEventListener('pointerdown', onPointerDown);
        header.addEventListener('pointermove', onPointerMove);
        header.addEventListener('pointerup', onPointerUp);
        header.addEventListener('pointercancel', onPointerUp);
    }
};

const initDeleteSheetGestures = () => {
    if (!(deleteModal instanceof HTMLElement)) return;

    const dialog = deleteModal.querySelector('.admin-modal__dialog');
    const backdrop = deleteModal.querySelector('.admin-modal__backdrop');
    const dragHandle = deleteModal.querySelector('[data-delete-drag-handle]');
    if (!(dialog instanceof HTMLElement)) return;

    let startY = 0;
    let currentDeltaY = 0;
    let isDragging = false;
    let startTime = 0;

    const onPointerDown = (event) => {
        if (!event.isPrimary) return;
        if (event.target instanceof Element && event.target.closest('button, a, input')) {
            return;
        }

        isDragging = true;
        startY = event.clientY;
        currentDeltaY = 0;
        startTime = performance.now();

        try {
            event.target.setPointerCapture?.(event.pointerId);
        } catch (_) {}
    };

    const onPointerMove = (event) => {
        if (!isDragging) return;

        const dy = event.clientY - startY;
        if (dy > 0) {
            currentDeltaY = dy;
            dialog.style.transform = `translateY(${dy}px)`;
            dialog.style.transition = 'none';
            if (backdrop instanceof HTMLElement) {
                const opacity = Math.max(0.12, 1 - (dy / 340));
                backdrop.style.opacity = String(opacity);
                backdrop.style.transition = 'none';
            }
        } else {
            currentDeltaY = 0;
            const resistance = dy * 0.18;
            dialog.style.transform = `translateY(${resistance}px)`;
            dialog.style.transition = 'none';
        }
    };

    const onPointerUp = (event) => {
        if (!isDragging) return;
        isDragging = false;

        try {
            event.target.releasePointerCapture?.(event.pointerId);
        } catch (_) {}

        const elapsed = performance.now() - startTime;
        const velocity = currentDeltaY / Math.max(elapsed, 1);

        if (currentDeltaY > 100 || (currentDeltaY > 35 && velocity > 0.45)) {
            dialog.style.transition = 'transform 220ms cubic-bezier(0.16, 1, 0.3, 1)';
            dialog.style.transform = 'translateY(100%)';
            if (backdrop instanceof HTMLElement) {
                backdrop.style.transition = 'opacity 220ms cubic-bezier(0.16, 1, 0.3, 1)';
                backdrop.style.opacity = '0';
            }
            window.setTimeout(() => {
                closeDeleteModal(true, true);
            }, 220);
        } else {
            dialog.style.transition = 'transform 260ms cubic-bezier(0.16, 1, 0.3, 1)';
            dialog.style.transform = 'translateY(0)';
            if (backdrop instanceof HTMLElement) {
                backdrop.style.transition = 'opacity 260ms cubic-bezier(0.16, 1, 0.3, 1)';
                backdrop.style.opacity = '1';
            }
            window.setTimeout(() => {
                dialog.style.transition = '';
                dialog.style.transform = '';
                if (backdrop instanceof HTMLElement) {
                    backdrop.style.transition = '';
                    backdrop.style.opacity = '';
                }
            }, 260);
        }
    };

    if (dragHandle instanceof HTMLElement) {
        dragHandle.addEventListener('pointerdown', onPointerDown);
        dragHandle.addEventListener('pointermove', onPointerMove);
        dragHandle.addEventListener('pointerup', onPointerUp);
        dragHandle.addEventListener('pointercancel', onPointerUp);
    }

    const header = deleteModal.querySelector('.admin-modal__header');
    if (header instanceof HTMLElement) {
        header.addEventListener('pointerdown', onPointerDown);
        header.addEventListener('pointermove', onPointerMove);
        header.addEventListener('pointerup', onPointerUp);
        header.addEventListener('pointercancel', onPointerUp);
    }
};

initBottomSheetGestures();
initDeleteSheetGestures();

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
