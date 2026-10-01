<dl class="admin-detail-list">
    <div><dt>{{ __('admin.catalog.fields.name_ar') }}</dt><dd>{{ $governorate->translate('ar', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.name_en') }}</dt><dd dir="ltr">{{ $governorate->translate('en', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.country') }}</dt><dd>{{ $governorate->country?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.cities_count') }}</dt><dd>{{ $governorate->cities_count }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.status') }}</dt><dd><span class="admin-status-badge admin-status-badge--{{ $governorate->is_active ? 'active' : 'inactive' }}">{{ $governorate->is_active ? __('admin.catalog.status.active') : __('admin.catalog.status.inactive') }}</span></dd></div>
</dl>
