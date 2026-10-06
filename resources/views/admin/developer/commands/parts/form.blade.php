<form class="admin-catalog-form developer-command-sheet" action="{{ $action }}" method="POST" data-catalog-form data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden role="alert"></div>
    <div class="admin-field">
        <label for="saved-command">{{ __('admin.developer_tools.command') }}</label>
        <div class="admin-field__control">
            <input id="saved-command" name="command" value="{{ old('command', $command?->command) }}"
                   dir="ltr" maxlength="255" required autocomplete="off" spellcheck="false"
                   placeholder="{{ __('admin.developer_tools.command_placeholder') }}" aria-describedby="saved-command-help">
        </div>
        <p id="saved-command-help" class="developer-page-note">{{ __('admin.developer_tools.command_format_help') }}</p>
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            <span>{{ __('admin.actions.cancel') }}</span>
        </button>
        <button class="admin-button admin-button--primary" type="submit">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            <span>{{ __('admin.developer_tools.save_command') }}</span>
        </button>
    </div>
</form>
