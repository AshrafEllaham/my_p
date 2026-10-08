<dl class="admin-detail-list">
    <div><dt>{{ __('admin.faqs.fields.type') }}</dt><dd>{{ __('admin.faqs.types.'.$faq->type->value) }}</dd></div>
    <div><dt>{{ __('admin.faqs.fields.question_ar') }}</dt><dd>{{ $faq->translate('ar', false)?->question }}</dd></div>
    <div><dt>{{ __('admin.faqs.fields.question_en') }}</dt><dd dir="ltr">{{ $faq->translate('en', false)?->question }}</dd></div>
    <div class="admin-detail-list__full"><dt>{{ __('admin.faqs.fields.answer_ar') }}</dt><dd>{{ $faq->translate('ar', false)?->answer }}</dd></div>
    <div class="admin-detail-list__full"><dt>{{ __('admin.faqs.fields.answer_en') }}</dt><dd dir="ltr">{{ $faq->translate('en', false)?->answer }}</dd></div>
</dl>
