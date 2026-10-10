@php
    $ar = $package?->translate('ar', false);
    $en = $package?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-admin-validate data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="ad-package-code">{{ __('admin.ad_packages.fields.code') }}</label>
            <div class="admin-field__control"><input id="ad-package-code" name="code" value="{{ old('code', $package?->code) }}" maxlength="100" pattern="[A-Za-z0-9_-]+" required dir="ltr" autocomplete="off"></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-duration">{{ __('admin.ad_packages.fields.duration_days') }}</label>
            <div class="admin-field__control"><input id="ad-package-duration" name="duration_days" type="number" min="1" max="65535" step="1" value="{{ old('duration_days', $package?->duration_days) }}" required dir="ltr"></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-price">{{ __('admin.ad_packages.fields.price') }}</label>
            <div class="admin-field__control"><input id="ad-package-price" name="price" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('price', $package?->price) }}" required dir="ltr"></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-currency">{{ __('admin.ad_packages.fields.currency') }}</label>
            <div class="admin-field__control"><input id="ad-package-currency" name="currency" value="{{ old('currency', $package?->currency ?? 'EGP') }}" minlength="3" maxlength="3" pattern="[A-Z]{3}" required dir="ltr" autocomplete="off"></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-name-ar">{{ __('admin.ad_packages.fields.name_ar') }}</label>
            <div class="admin-field__control"><input id="ad-package-name-ar" name="ar[name]" value="{{ old('ar.name', $ar?->name) }}" maxlength="255" required></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-name-en">{{ __('admin.ad_packages.fields.name_en') }}</label>
            <div class="admin-field__control"><input id="ad-package-name-en" name="en[name]" value="{{ old('en.name', $en?->name) }}" maxlength="255" required dir="ltr"></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-description-ar">{{ __('admin.ad_packages.fields.description_ar') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="ad-package-description-ar" name="ar[description]" maxlength="5000">{{ old('ar.description', $ar?->description) }}</textarea></div>
        </div>
        <div class="admin-field">
            <label for="ad-package-description-en">{{ __('admin.ad_packages.fields.description_en') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="ad-package-description-en" name="en[description]" maxlength="5000" dir="ltr">{{ old('en.description', $en?->description) }}</textarea></div>
        </div>
        @include('admin.catalog.parts.active-field', ['isActive' => old('is_active', $package?->is_active ?? true)])
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" /></svg>
            <span>{{ __('admin.actions.cancel') }}</span>
        </button>
        <button class="admin-button admin-button--primary" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12" /></svg>
            <span>{{ __('admin.actions.save') }}</span>
        </button>
    </div>
</form>
