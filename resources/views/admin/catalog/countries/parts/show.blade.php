<dl class="admin-detail-list">
    <div><dt>{{ __('admin.catalog.fields.name_ar') }}</dt><dd>{{ $country->translate('ar', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.name_en') }}</dt><dd dir="ltr">{{ $country->translate('en', false)?->name }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.code') }}</dt><dd dir="ltr">{{ $country->code }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.phone_code') }}</dt><dd dir="ltr">{{ $country->phone_code ?: '—' }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.flag') }}</dt><dd>{{ $country->flag ?: '—' }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.governorates_count') }}</dt><dd>{{ $country->governorates_count }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.status') }}</dt><dd><span class="admin-status-badge admin-status-badge--{{ $country->is_active ? 'active' : 'inactive' }}">{{ $country->is_active ? __('admin.catalog.status.active') : __('admin.catalog.status.inactive') }}</span></dd></div>
</dl>
