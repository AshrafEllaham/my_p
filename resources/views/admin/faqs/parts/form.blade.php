@php
    $ar = $faq?->translate('ar', false);
    $en = $faq?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field admin-form-grid__full">
            <label for="faq-type">{{ __('admin.faqs.fields.type') }}</label>
            <div class="admin-field__control admin-field__control--select">
                <select id="faq-type" name="type" required>
                    <option value="">{{ __('admin.faqs.choose_type') }}</option>
                    @foreach (\App\Enums\AccountTypeEnum::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $faq?->type?->value) === $type->value)>
                            {{ __('admin.faqs.types.'.$type->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="admin-field">
            <label for="faq-ar-question">{{ __('admin.faqs.fields.question_ar') }}</label>
            <div class="admin-field__control"><input id="faq-ar-question" name="ar[question]" value="{{ old('ar.question', $ar?->question) }}" required></div>
        </div>
        <div class="admin-field">
            <label for="faq-en-question">{{ __('admin.faqs.fields.question_en') }}</label>
            <div class="admin-field__control"><input id="faq-en-question" name="en[question]" value="{{ old('en.question', $en?->question) }}" dir="ltr" required></div>
        </div>
        <div class="admin-field">
            <label for="faq-ar-answer">{{ __('admin.faqs.fields.answer_ar') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="faq-ar-answer" name="ar[answer]" required>{{ old('ar.answer', $ar?->answer) }}</textarea></div>
        </div>
        <div class="admin-field">
            <label for="faq-en-answer">{{ __('admin.faqs.fields.answer_en') }}</label>
            <div class="admin-field__control admin-field__control--textarea"><textarea id="faq-en-answer" name="en[answer]" dir="ltr" required>{{ old('en.answer', $en?->answer) }}</textarea></div>
        </div>
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" /></svg>
            <span>{{ __('admin.actions.cancel') }}</span>
        </button>
        <button class="admin-button admin-button--primary" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12" /></svg>
            <span>{{ __('admin.actions.save') }}</span>
        </button>
    </div>
</form>
