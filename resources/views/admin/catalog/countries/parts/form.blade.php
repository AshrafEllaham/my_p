@php
    $ar = $country?->translate('ar', false);
    $en = $country?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-admin-validate data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="country-ar-name">{{ __('admin.catalog.fields.name_ar') }}</label>
            <div class="admin-field__control"><input id="country-ar-name" name="ar[name]" value="{{ old('ar.name', $ar?->name) }}" maxlength="255" required></div>
        </div>
        <div class="admin-field">
            <label for="country-en-name">{{ __('admin.catalog.fields.name_en') }}</label>
            <div class="admin-field__control"><input id="country-en-name" name="en[name]" value="{{ old('en.name', $en?->name) }}" maxlength="255" dir="ltr" required></div>
        </div>
        <div class="admin-field">
            <label for="country-code">{{ __('admin.catalog.fields.code') }}</label>
            <div class="admin-field__control"><input id="country-code" name="code" value="{{ old('code', $country?->code) }}" minlength="2" maxlength="3" pattern="[A-Z]+" dir="ltr" required></div>
        </div>
        <div class="admin-field">
            <label for="country-phone-code">{{ __('admin.catalog.fields.phone_code') }}</label>
            <div class="admin-field__control"><input id="country-phone-code" name="phone_code" value="{{ old('phone_code', $country?->phone_code) }}" maxlength="10" pattern="\+?[0-9]+" dir="ltr"></div>
        </div>
        <div class="admin-field admin-form-grid__full">
            <label for="country-flag">{{ __('admin.catalog.fields.flag') }}</label>
            <div class="admin-field__control"><input id="country-flag" name="flag" value="{{ old('flag', $country?->flag) }}" maxlength="50"></div>
        </div>
        @include('admin.catalog.parts.active-field', ['isActive' => $country?->is_active ?? true])
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
