@php
    $ar = $city?->translate('ar', false);
    $en = $city?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="city-ar-name">{{ __('admin.catalog.fields.name_ar') }}</label>
            <div class="admin-field__control"><input id="city-ar-name" name="ar[name]" value="{{ old('ar.name', $ar?->name) }}" required></div>
        </div>
        <div class="admin-field">
            <label for="city-en-name">{{ __('admin.catalog.fields.name_en') }}</label>
            <div class="admin-field__control"><input id="city-en-name" name="en[name]" value="{{ old('en.name', $en?->name) }}" dir="ltr" required></div>
        </div>
        <div class="admin-field admin-form-grid__full">
            <label for="city-governorate">{{ __('admin.catalog.fields.governorate') }}</label>
            <div class="admin-field__control admin-field__control--select">
                <select id="city-governorate" name="governorate_id" required>
                    <option value="">{{ __('admin.catalog.fields.choose_governorate') }}</option>
                    @foreach ($governorates as $governorateOption)
                        <option value="{{ $governorateOption->id }}" @selected((int) old('governorate_id', $city?->governorate_id) === $governorateOption->id)>{{ $governorateOption->country_name }} — {{ $governorateOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @include('admin.catalog.parts.active-field', ['isActive' => $city?->is_active ?? true])
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>{{ __('admin.actions.cancel') }}</button>
        <button class="admin-button admin-button--primary" type="submit">{{ __('admin.actions.save') }}</button>
    </div>
</form>
