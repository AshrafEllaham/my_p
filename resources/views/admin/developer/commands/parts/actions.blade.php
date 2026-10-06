<div class="admin-table-actions">
    <button class="admin-action-button admin-action-button--edit" type="button"
            data-modal-url="{{ route('admin.developer.commands.edit', $command->id) }}" data-modal-heading="{{ __('admin.developer_tools.edit_command') }}"
            title="{{ __('admin.developer_tools.edit_command') }}" aria-label="{{ __('admin.developer_tools.edit_command') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </button>
    <button class="admin-action-button admin-action-button--delete" type="button"
            data-delete-url="{{ route('admin.developer.commands.destroy', $command->id) }}"
            title="{{ __('admin.developer_tools.delete_command') }}" aria-label="{{ __('admin.developer_tools.delete_command') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>
    </button>
</div>
