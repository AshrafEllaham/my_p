<div class="admin-table-actions">
    <button class="admin-action-button admin-action-button--view" type="button"
            data-modal-url="{{ $showUrl }}" data-modal-heading="{{ __('admin.actions.show') }}"
            title="{{ __('admin.actions.show') }}" aria-label="{{ __('admin.actions.show') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
    </button>
    <button class="admin-action-button admin-action-button--edit" type="button"
            data-modal-url="{{ $editUrl }}" data-modal-heading="{{ __('admin.actions.edit') }}"
            title="{{ __('admin.actions.edit') }}" aria-label="{{ __('admin.actions.edit') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </button>
    <button class="admin-action-button admin-action-button--delete" type="button"
            data-delete-url="{{ $deleteUrl }}"
            title="{{ __('admin.actions.delete') }}" aria-label="{{ __('admin.actions.delete') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>
    </button>
</div>
