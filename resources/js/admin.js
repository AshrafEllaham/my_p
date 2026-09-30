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
