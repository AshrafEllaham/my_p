<form class="admin-catalog-form" action="{{ $action }}" method="POST" enctype="multipart/form-data" data-catalog-form data-admin-validate data-no-loader>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field admin-form-grid__full">
            <label for="banner-file">{{ __('admin.banners.fields.file') }}</label>
            <input
                id="banner-file"
                class="dropify"
                name="file"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @required($banner === null)
                data-default-file="{{ $banner ? \Illuminate\Support\Facades\Storage::disk('public')->url($banner->file) : '' }}"
                data-dropify-default="{{ __('admin.banners.drop_file') }}"
                data-dropify-replace="{{ __('admin.banners.replace_file') }}"
                data-dropify-remove="{{ __('admin.banners.clear_file') }}"
                data-dropify-error="{{ __('admin.banners.dropify_error') }}"
                data-dropify-file-size-error="{{ __('admin.banners.file_size_error') }}"
                data-dropify-file-type-error="{{ __('admin.banners.file_type_error') }}"
                aria-describedby="banner-file-help"
            >
            <small id="banner-file-help" class="admin-field__help">{{ __('admin.banners.file_help') }}</small>
        </div>
        <div class="admin-field admin-form-grid__full">
            <label for="banner-type">{{ __('admin.banners.fields.type') }}</label>
            <div class="admin-field__control admin-field__control--select">
                <select id="banner-type" name="type" required>
                    <option value="">{{ __('admin.banners.choose_type') }}</option>
                    @foreach (\App\Enums\AccountTypeEnum::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $banner?->type?->value) === $type->value)>
                            {{ __('admin.banners.types.'.$type->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            <span>{{ __('admin.actions.cancel') }}</span>
        </button>
        <button class="admin-button admin-button--primary" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ __('admin.actions.save') }}</span>
        </button>
    </div>
</form>
