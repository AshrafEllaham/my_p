@php
    $ar = $reason?->translate('ar', false);
    $en = $reason?->translate('en', false);
@endphp
<form class="admin-catalog-form" action="{{ $action }}" method="POST" data-catalog-form data-admin-validate data-no-loader>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="admin-form-errors" data-form-errors hidden></div>
    <div class="admin-form-grid">
        <div class="admin-field admin-form-grid__full">
            <label for="reason-type">{{ __('admin.report_reasons.fields.type') }}</label>
            <div class="admin-field__control admin-field__control--select">
                <select id="reason-type" name="type" required>
                    <option value="">{{ __('admin.report_reasons.choose_type') }}</option>
                    @foreach (\App\Enums\UserReportTypeEnum::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $reason?->type?->value) === $type->value)>
                            {{ __('admin.report_reasons.types.'.$type->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="admin-field">
            <label for="reason-ar-title">{{ __('admin.report_reasons.fields.title_ar') }}</label>
            <div class="admin-field__control"><input id="reason-ar-title" name="ar[title]" value="{{ old('ar.title', $ar?->title) }}" maxlength="255" required></div>
        </div>
        <div class="admin-field">
            <label for="reason-en-title">{{ __('admin.report_reasons.fields.title_en') }}</label>
            <div class="admin-field__control"><input id="reason-en-title" name="en[title]" value="{{ old('en.title', $en?->title) }}" maxlength="255" dir="ltr" required></div>
        </div>
    </div>
    <div class="admin-form-actions">
        <button class="admin-button admin-button--secondary" type="button" data-modal-close>{{ __('admin.actions.cancel') }}</button>
        <button class="admin-button admin-button--primary" type="submit">{{ __('admin.actions.save') }}</button>
    </div>
</form>
