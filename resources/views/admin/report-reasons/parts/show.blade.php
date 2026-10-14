<dl class="admin-detail-list">
    <div><dt>{{ __('admin.report_reasons.fields.type') }}</dt><dd>{{ __('admin.report_reasons.types.'.$reason->type->value) }}</dd></div>
    <div><dt>{{ __('admin.report_reasons.fields.title_ar') }}</dt><dd>{{ $reason->translate('ar', false)?->title }}</dd></div>
    <div><dt>{{ __('admin.report_reasons.fields.title_en') }}</dt><dd dir="ltr">{{ $reason->translate('en', false)?->title }}</dd></div>
</dl>
