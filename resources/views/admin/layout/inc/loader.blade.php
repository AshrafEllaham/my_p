<div class="admin-page-loader" data-page-loader role="status" aria-live="polite" aria-label="{{ __('admin.loading.label') }}" hidden>
    <div class="admin-page-loader__backdrop"></div>
    <div class="admin-page-loader__content">
        <span class="admin-page-loader__brand" aria-hidden="true">
            <span class="admin-page-loader__orbit"></span>
            <span class="admin-page-loader__mark">
                <x-admin.brand-mark />
            </span>
        </span>
        <span class="admin-page-loader__copy">
            <strong>{{ __('admin.app_name') }}</strong>
            <small>{{ __('admin.loading.message') }}</small>
        </span>
        <span class="admin-page-loader__track" aria-hidden="true">
            <span></span>
        </span>
    </div>
</div>
