<dl class="admin-detail-list">
    @if ($isSubCategory)
        <div><dt>{{ __('admin.catalog.fields.main_category') }}</dt><dd>{{ $category->parent?->name }}</dd></div>
    @endif
    <div><dt>{{ __('admin.catalog.fields.name_ar') }}</dt><dd>{{ $category->translate('ar', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.name_en') }}</dt><dd dir="ltr">{{ $category->translate('en', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.slug') }}</dt><dd dir="ltr">{{ $category->slug }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.icon') }}</dt><dd dir="ltr">{{ $category->icon ?: '—' }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.sort_order') }}</dt><dd>{{ $category->sort_order }}</dd></div>
    @unless ($isSubCategory)
        <div><dt>{{ __('admin.catalog.fields.sub_categories_count') }}</dt><dd>{{ $category->children_count }}</dd></div>
    @endunless
    <div class="admin-detail-list__full"><dt>{{ __('admin.catalog.fields.description_ar') }}</dt><dd>{{ $category->translate('ar', false)?->description ?: '—' }}</dd></div>
    <div class="admin-detail-list__full"><dt>{{ __('admin.catalog.fields.description_en') }}</dt><dd dir="ltr">{{ $category->translate('en', false)?->description ?: '—' }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.status') }}</dt><dd><span class="admin-status-badge admin-status-badge--{{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? __('admin.catalog.status.active') : __('admin.catalog.status.inactive') }}</span></dd></div>
</dl>
