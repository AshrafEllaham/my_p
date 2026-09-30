<nav class="admin-breadcrumb" aria-label="breadcrumb">
    <a href="{{ route('admin.index') }}">{{ __('admin.dashboard') }}</a>
    @foreach (($breadcrumbs ?? []) as $breadcrumb)
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        @if (! empty($breadcrumb['url']))
            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
        @else
            <span aria-current="page">{{ $breadcrumb['label'] }}</span>
        @endif
    @endforeach
</nav>
