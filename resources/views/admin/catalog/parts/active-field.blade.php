<div class="admin-field admin-form-grid__full">
    <label class="admin-switch-field">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $isActive))>
        <span class="admin-switch-field__track" aria-hidden="true"></span>
        <span>{{ __('admin.catalog.fields.is_active') }}</span>
    </label>
</div>
