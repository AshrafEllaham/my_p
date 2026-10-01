@php
    $ar = $category?->translate('ar', false);
    $en = $category?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        @if ($isSubCategory)
            <div class="admin-field admin-form-grid__full">
                <label for="category-parent">{{ __('admin.catalog.fields.main_category') }}</label>
                <div class="admin-field__control admin-field__control--select">
                    <select id="category-parent" name="parent_id" required>
                        <option value="">{{ __('admin.catalog.fields.choose_main_category') }}</option>
                        @foreach ($parents as $parentOption)
                            <option value="{{ $parentOption->id }}" @selected((int) old('parent_id', $category?->parent_id) === $parentOption->id)>{{ $parentOption->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        <div class="admin-field">
            <label for="category-ar-name">{{ __('admin.catalog.fields.name_ar') }}</label>
            <div class="admin-field__control"><input id="category-ar-name" name="ar[name]" value="{{ old('ar.name', $ar?->name) }}" required></div>
        </div>
        <div class="admin-field">
            <label for="category-en-name">{{ __('admin.catalog.fields.name_en') }}</label>
            <div class="admin-field__control"><input id="category-en-name" name="en[name]" value="{{ old('en.name', $en?->name) }}" dir="ltr" required></div>
        </div>
        <div class="admin-field admin-form-grid__full">
            <label for="category-slug">{{ __('admin.catalog.fields.slug') }}</label>
            <div class="admin-field__control"><input id="category-slug" name="slug" value="{{ old('slug', $category?->slug) }}" dir="ltr" required></div>
        </div>
        <div class="admin-field">
            <label for="category-icon">{{ __('admin.catalog.fields.icon') }}</label>
            <div class="admin-field__control"><input id="category-icon" name="icon" value="{{ old('icon', $category?->icon) }}" dir="ltr"></div>
        </div>
        <div class="admin-field">
            <label for="category-sort-order">{{ __('admin.catalog.fields.sort_order') }}</label>
            <div class="admin-field__control"><input id="category-sort-order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" required></div>
        </div>
        <div class="admin-field">
            <label for="category-ar-description">{{ __('admin.catalog.fields.description_ar') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="category-ar-description" name="ar[description]">{{ old('ar.description', $ar?->description) }}</textarea></div>
        </div>
        <div class="admin-field">
            <label for="category-en-description">{{ __('admin.catalog.fields.description_en') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="category-en-description" name="en[description]" dir="ltr">{{ old('en.description', $en?->description) }}</textarea></div>
        </div>
        @include('admin.catalog.parts.active-field', ['isActive' => $category?->is_active ?? true])
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
