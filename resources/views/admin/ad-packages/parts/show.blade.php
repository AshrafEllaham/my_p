@php
    $ar = $package->translate('ar', false);
    $en = $package->translate('en', false);
@endphp
<dl class="admin-detail-list">
    <div><dt>{{ __('admin.ad_packages.fields.code') }}</dt><dd dir="ltr">{{ $package->code }}</dd></div>
    <div><dt>{{ __('admin.ad_packages.fields.duration_days') }}</dt><dd>{{ $package->duration_days }}</dd></div>
    <div><dt>{{ __('admin.ad_packages.fields.price') }}</dt><dd dir="ltr">{{ $package->price }} {{ $package->currency }}</dd></div>
    <div><dt>{{ __('admin.catalog.fields.status') }}</dt><dd>{{ $package->is_active ? __('admin.catalog.status.active') : __('admin.catalog.status.inactive') }}</dd></div>
    <div><dt>{{ __('admin.ad_packages.fields.name_ar') }}</dt><dd>{{ $ar?->name }}</dd></div>
    <div><dt>{{ __('admin.ad_packages.fields.name_en') }}</dt><dd dir="ltr">{{ $en?->name }}</dd></div>
    <div class="admin-detail-list__full"><dt>{{ __('admin.ad_packages.fields.description_ar') }}</dt><dd>{{ $ar?->description }}</dd></div>
    <div class="admin-detail-list__full"><dt>{{ __('admin.ad_packages.fields.description_en') }}</dt><dd dir="ltr">{{ $en?->description }}</dd></div>
</dl>
