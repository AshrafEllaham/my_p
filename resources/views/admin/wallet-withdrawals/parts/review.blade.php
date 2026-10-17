<dl class="admin-detail-list">
    <div><dt>{{ __('admin.wallet_withdrawals.fields.owner') }}</dt><dd>{{ $withdrawalRequest->wallet?->user?->name ?? __('admin.wallet_withdrawals.not_available') }}</dd></div>
    <div><dt>{{ __('admin.wallet_withdrawals.fields.amount') }}</dt><dd dir="ltr">{{ $withdrawalRequest->amount }} {{ $withdrawalRequest->currency }}</dd></div>
    <div><dt>{{ __('admin.wallet_withdrawals.fields.method') }}</dt><dd>{{ __('admin.wallet_withdrawals.methods.'.$withdrawalRequest->method->value) }}</dd></div>
    <div><dt>{{ __('admin.wallet_withdrawals.fields.destination') }}</dt><dd dir="ltr">{{ $withdrawalRequest->destination }}</dd></div>
    <div><dt>{{ __('admin.wallet_withdrawals.fields.submitted_at') }}</dt><dd>{{ $withdrawalRequest->created_at?->format('Y-m-d H:i') }}</dd></div>
</dl>

<div class="admin-ad-review__actions">
    <form class="admin-catalog-form" action="{{ $approveUrl }}" method="POST" data-catalog-form data-admin-validate data-review-action data-no-loader>
        @csrf
        <div class="admin-form-errors" data-form-errors hidden role="alert"></div>
        <button class="admin-button admin-button--primary" type="submit">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            <span>{{ __('admin.wallet_withdrawals.approve') }}</span>
        </button>
    </form>

    <form class="admin-catalog-form" action="{{ $rejectUrl }}" method="POST" data-catalog-form data-admin-validate data-review-action data-no-loader>
        @csrf
        <div class="admin-form-errors" data-form-errors hidden role="alert"></div>
        <div class="admin-field">
            <label for="withdrawal-rejection-reason">{{ __('admin.wallet_withdrawals.fields.rejection_reason') }}</label>
            <div class="admin-field__control admin-field__control--textarea">
                <textarea id="withdrawal-rejection-reason" name="reason" maxlength="1000" required></textarea>
            </div>
        </div>
        <button class="admin-button admin-button--danger" type="submit">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            <span>{{ __('admin.wallet_withdrawals.reject') }}</span>
        </button>
    </form>
</div>
