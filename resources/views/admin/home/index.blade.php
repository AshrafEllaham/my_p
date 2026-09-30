@extends('admin.layout.indexs.index')

@section('page-title', $oneObjectTitle)

@section('content')
    <section class="admin-hero">
        <div>
            <span class="admin-eyebrow">{{ __('admin.overview') }}</span>
            <h1>{{ __('admin.welcome') }}</h1>
            <p>{{ __('admin.welcome_description') }}</p>
        </div>
        <div class="admin-hero__mark" aria-hidden="true">
            <svg viewBox="0 0 64 64"><path d="M13 42c7-17 18-25 38-25M41 10l10 7-4 13"/></svg>
        </div>
    </section>

    <section class="admin-section" aria-labelledby="stats-title">
        <div class="admin-section__heading">
            <div><span class="admin-eyebrow">{{ __('admin.dashboard') }}</span><h2 id="stats-title">{{ __('admin.overview') }}</h2></div>
        </div>
        <div class="admin-stats-grid">
            @foreach ($dashboard['stats'] as $stat)
                <article class="admin-stat-card admin-stat-card--{{ $stat['tone'] }}">
                    <span class="admin-stat-card__label">{{ $stat['label'] }}</span>
                    <strong class="admin-stat-card__value">{{ number_format($stat['value']) }}</strong>
                    <span class="admin-stat-card__meta">—</span>
                </article>
            @endforeach
        </div>
    </section>

    <section class="admin-panel" aria-labelledby="activity-title">
        <div class="admin-section__heading"><h2 id="activity-title">{{ __('admin.recent_activity') }}</h2></div>
        @forelse ($dashboard['activity'] as $activity)
            <article class="admin-activity-item">
                <span class="admin-activity-item__dot"></span>
                <div><strong>{{ $activity['title'] }}</strong><p>{{ $activity['description'] }}</p></div>
                <time>{{ $activity['time'] }}</time>
            </article>
        @empty
            <div class="admin-empty-state">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4zM8 9h8M8 13h5"/></svg>
                <p>{{ __('admin.no_activity') }}</p>
            </div>
        @endforelse
    </section>
@endsection
