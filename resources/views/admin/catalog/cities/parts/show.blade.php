<dl class="admin-detail-list">
    <div><dt>{{ __('admin.catalog.fields.name_ar') }}</dt><dd>{{ $city->translate('ar', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.name_en') }}</dt><dd dir="ltr">{{ $city->translate('en', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.governorate') }}</dt><dd>{{ $city->governorate?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.country') }}</dt><dd>{{ $city->governorate?->country?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.status') }}</dt><dd><span class="admin-status-badge admin-status-badge--{{ $city->is_active ? 'active' : 'inactive' }}">{{ $city->is_active ? __('admin.catalog.status.active') : __('admin.catalog.status.inactive') }}</span></dd></div>
</dl>
