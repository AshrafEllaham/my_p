@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.commands'))

@section('content')
    <section class="admin-hero">
        <div>
            <span class="admin-eyebrow">{{ __('admin.developer_tools.eyebrow') }}</span>
            <h1>{{ __('admin.developer_tools.commands') }}</h1>
            <p>{{ __('admin.developer_tools.commands_description') }}</p>
        </div>
        <a class="admin-button admin-button--primary" href="{{ route('admin.developer.terminal.index') }}">
            {{ __('admin.developer_tools.open_terminal') }}
        </a>
    </section>

    <section class="admin-section" aria-labelledby="developer-commands-title">
        <div class="admin-section__heading">
            <div>
                <span class="admin-eyebrow">{{ __('admin.developer_tools.approved') }}</span>
                <h2 id="developer-commands-title">{{ __('admin.developer_tools.command_list') }}</h2>
            </div>
            <span class="admin-stat-card__meta">{{ __('admin.developer_tools.command_count', ['count' => $commands->count()]) }}</span>
        </div>

        <div class="admin-panel">
            <div class="developer-command-list">
                @foreach ($commands as $command)
                    <article class="developer-command-item">
                        <span class="developer-command-item__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <code dir="ltr">{{ $command->command }}</code>
                        <span class="developer-command-item__status">{{ __('admin.developer_tools.ready') }}</span>
                    </article>
                @endforeach
            </div>
            <p class="developer-command-note">{{ __('admin.developer_tools.safety_note') }}</p>
        </div>
    </section>
@endsection

@push('css')
    <style>
        .developer-command-list { display: grid; gap: 10px; }
        .developer-command-item { display: flex; align-items: center; gap: 14px; min-width: 0; padding: 13px 15px; border: 1px solid var(--border); border-radius: 11px; background: var(--surface, #fff); }
        .developer-command-item__index { color: var(--muted); font-size: 11px; font-weight: 800; }
        .developer-command-item code { flex: 1; overflow-wrap: anywhere; color: var(--ink); font-size: 13px; }
        .developer-command-item__status { color: var(--success); font-size: 11px; font-weight: 800; white-space: nowrap; }
        .developer-command-note { margin: 18px 0 0; color: var(--muted); font-size: 12px; line-height: 1.7; }
        @media (max-width: 560px) { .developer-command-item { align-items: flex-start; flex-wrap: wrap; } .developer-command-item code { flex-basis: calc(100% - 40px); } }
    </style>
@endpush
