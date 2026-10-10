@php
    $ar = $governorate?->translate('ar', false);
    $en = $governorate?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-admin-validate data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="governorate-ar-name">{{ __('admin.catalog.fields.name_ar') }}</label>
            <div class="admin-field__control"><input id="governorate-ar-name" name="ar[name]" value="{{ old('ar.name', $ar?->name) }}" maxlength="255" required></div>
        </div>
        <div class="admin-field">
            <label for="governorate-en-name">{{ __('admin.catalog.fields.name_en') }}</label>
            <div class="admin-field__control"><input id="governorate-en-name" name="en[name]" value="{{ old('en.name', $en?->name) }}" maxlength="255" dir="ltr" required></div>
        </div>
        <div class="admin-field admin-form-grid__full">
            <label for="governorate-country">{{ __('admin.catalog.fields.country') }}</label>
            <div class="admin-field__control admin-field__control--select">
                <select id="governorate-country" name="country_id" required>
                    <option value="">{{ __('admin.catalog.fields.choose_country') }}</option>
                    @foreach ($countries as $countryOption)
                        <option value="{{ $countryOption->id }}" @selected((int) old('country_id', $governorate?->country_id) === $countryOption->id)>{{ $countryOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @include('admin.catalog.parts.active-field', ['isActive' => $governorate?->is_active ?? true])
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
