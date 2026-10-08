<div class="admin-banner-show">
    <img class="admin-banner-show__image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->file) }}" alt="{{ __('admin.banners.image_alt') }}">
    <dl class="admin-detail-list">
        <div><dt>{{ __('admin.banners.fields.type') }}</dt><dd>{{ __('admin.banners.types.'.$banner->type->value) }}</dd></div>
        <div><dt>{{ __('admin.banners.fields.file') }}</dt><dd dir="ltr">{{ $banner->file }}</dd></div>
        <div><dt>{{ __('admin.banners.fields.created_at') }}</dt><dd>{{ $banner->created_at?->format('Y-m-d H:i') }}</dd></div>
    </dl>
</div>
