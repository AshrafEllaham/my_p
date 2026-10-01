<div class="admin-modal" data-admin-modal hidden>
    <button class="admin-modal__backdrop" type="button" data-modal-close aria-label="{{ __('admin.actions.close') }}"></button>
    <section class="admin-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="global-modal-title">
        <header class="admin-modal__header">
            <h2 id="global-modal-title" data-modal-title></h2>
            <button class="admin-icon-button" type="button" data-modal-close aria-label="{{ __('admin.actions.close') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="admin-modal__content" data-modal-content></div>
    </section>
</div>

<div class="admin-modal admin-delete-modal" data-delete-modal hidden>
    <button class="admin-modal__backdrop" type="button" data-delete-cancel aria-label="{{ __('admin.actions.close') }}"></button>
    <section class="admin-modal__dialog admin-delete-modal__dialog" role="alertdialog" aria-modal="true"
             aria-labelledby="delete-modal-title" aria-describedby="delete-modal-description">
        <button class="admin-delete-modal__close admin-icon-button" type="button" data-delete-cancel aria-label="{{ __('admin.actions.close') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <div class="admin-delete-modal__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>
        </div>
        <div class="admin-delete-modal__copy">
            <h2 id="delete-modal-title">{{ __('admin.catalog.messages.delete_title') }}</h2>
            <p id="delete-modal-description">{{ __('admin.catalog.messages.delete_confirm') }}</p>
            <small>{{ __('admin.catalog.messages.delete_warning') }}</small>
        </div>
        <div class="admin-alert admin-alert--danger admin-delete-modal__error" data-delete-error hidden role="alert"></div>
        <div class="admin-delete-modal__actions">
            <button class="admin-button admin-button--secondary" type="button" data-delete-cancel>{{ __('admin.actions.cancel') }}</button>
            <button class="admin-button admin-button--danger" type="button" data-delete-confirm>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>
                <span>{{ __('admin.actions.delete') }}</span>
            </button>
        </div>
    </section>
</div>
