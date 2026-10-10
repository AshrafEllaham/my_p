@php
    $mediaUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($ad->media_path);
@endphp

<div class="admin-ad-review">
    <section class="admin-ad-review__media" aria-label="{{ __('admin.ads.media_preview') }}">
        @if ($ad->media_type === \App\Enums\MediaTypeEnum::Video)
            <video controls preload="metadata" src="{{ $mediaUrl }}"></video>
        @elseif ($ad->media_type === \App\Enums\MediaTypeEnum::Image)
            <img src="{{ $mediaUrl }}" alt="{{ $ad->title }}">
        @else
            <a class="admin-button admin-button--secondary" href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                {{ __('admin.ads.open_media') }}
            </a>
        @endif
    </section>

    <dl class="admin-detail-list">
        <div><dt>{{ __('admin.ads.fields.title') }}</dt><dd>{{ $ad->title }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.store') }}</dt><dd>{{ $ad->store?->owner?->name ?? __('admin.ads.not_available') }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.product') }}</dt><dd>{{ $ad->product?->name ?? __('admin.ads.not_available') }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.category') }}</dt><dd>{{ $ad->category?->name ?? __('admin.ads.not_available') }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.placement') }}</dt><dd>{{ __('admin.ads.placements.'.$ad->placement->value) }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.action') }}</dt><dd>{{ __('admin.ads.actions.'.$ad->action->value) }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.cost') }}</dt><dd dir="ltr">{{ $ad->cost }} {{ $ad->currency }}</dd></div>
        <div><dt>{{ __('admin.ads.fields.duration') }}</dt><dd>{{ $ad->adPackage->duration_days }} {{ __('admin.ads.days') }}</dd></div>
        @if ($ad->caption)
            <div class="admin-detail-list__full"><dt>{{ __('admin.ads.fields.caption') }}</dt><dd>{{ $ad->caption }}</dd></div>
        @endif
    </dl>

    <div class="admin-ad-review__actions">
        <form class="admin-catalog-form" action="{{ $approveUrl }}" method="POST" data-catalog-form data-admin-validate data-review-action data-no-loader>
            @csrf
            <div class="admin-form-errors" data-form-errors hidden role="alert"></div>
            <button class="admin-button admin-button--primary" type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                <span>{{ __('admin.ads.approve') }}</span>
            </button>
        </form>

        <form class="admin-catalog-form" action="{{ $rejectUrl }}" method="POST" data-catalog-form data-admin-validate data-review-action data-no-loader>
            @csrf
            <div class="admin-form-errors" data-form-errors hidden role="alert"></div>
            <div class="admin-field">
                <label for="ad-rejection-reason">{{ __('admin.ads.fields.rejection_reason') }}</label>
                <div class="admin-field__control admin-field__control--textarea">
                    <textarea id="ad-rejection-reason" name="reason" maxlength="1000" required></textarea>
                </div>
            </div>
            <button class="admin-button admin-button--danger" type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                <span>{{ __('admin.ads.reject') }}</span>
            </button>
        </form>
    </div>
</div>
