@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.commands'))

@section('content')
    <section class="admin-hero developer-page-hero">
        <div>
            <span class="admin-eyebrow">{{ __('admin.developer_tools.eyebrow') }}</span>
            <h1>{{ __('admin.developer_tools.commands') }}</h1>
            <p>{{ __('admin.developer_tools.commands_description') }}</p>
        </div>
        <a class="admin-button admin-button--primary" href="{{ route('admin.developer.terminal.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m7 9 3 3-3 3M13 15h4"/></svg>
            {{ __('admin.developer_tools.open_terminal') }}
        </a>
    </section>

    <section class="admin-section developer-command-section" aria-labelledby="developer-commands-title">
        <div class="developer-section-heading">
            <div>
                <span class="admin-eyebrow">{{ __('admin.developer_tools.approved') }}</span>
                <h2 id="developer-commands-title">{{ __('admin.developer_tools.command_list') }}</h2>
            </div>
            <span class="developer-count-badge">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
                {{ __('admin.developer_tools.command_count', ['count' => $commands->count()]) }}
            </span>
        </div>

        @if (session('success'))
            <div class="admin-alert admin-alert--success developer-command-flash" role="status">{{ session('success') }}</div>
        @endif
        @error('command')
            <div class="admin-alert developer-command-flash developer-command-flash--error" role="alert">{{ $message }}</div>
        @enderror

        <div class="admin-panel developer-command-panel">
            <details class="developer-command-create">
                <summary>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('admin.developer_tools.add_command') }}
                </summary>
                <form method="post" action="{{ route('admin.developer.commands.store') }}" class="developer-command-form">
                    @csrf
                    <label for="new-command">{{ __('admin.developer_tools.command') }}</label>
                    <input id="new-command" class="admin-field__control" type="text" name="command" value="{{ old('command') }}" maxlength="255" required placeholder="{{ __('admin.developer_tools.command_placeholder') }}">
                    <p>{{ __('admin.developer_tools.command_format_help') }}</p>
                    <button type="submit" class="admin-button admin-button--primary">{{ __('admin.developer_tools.save_command') }}</button>
                </form>
            </details>

            @forelse ($commands as $command)
                <article class="developer-command-record">
                    <div class="developer-command-item">
                        <span class="developer-command-item__index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="developer-command-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
                        </span>
                        <code dir="ltr">{{ $command->command }}</code>
                        <span class="developer-command-item__status"><i aria-hidden="true"></i>{{ __('admin.developer_tools.ready') }}</span>
                        <details class="developer-command-edit">
                            <summary>{{ __('admin.developer_tools.edit_command') }}</summary>
                            <form method="post" action="{{ route('admin.developer.commands.update', $command->id) }}" class="developer-command-form">
                                @csrf
                                @method('PUT')
                                <label for="command-{{ $command->id }}">{{ __('admin.developer_tools.command') }}</label>
                                <input id="command-{{ $command->id }}" class="admin-field__control" type="text" name="command" value="{{ $command->command }}" maxlength="255" required>
                                <p>{{ __('admin.developer_tools.command_format_help') }}</p>
                                <button type="submit" class="admin-button admin-button--primary">{{ __('admin.developer_tools.save_command') }}</button>
                            </form>
                        </details>
                        <form method="post" action="{{ route('admin.developer.commands.destroy', $command->id) }}" data-confirm="{{ __('admin.developer_tools.confirm_delete') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-button admin-button--secondary developer-command-delete">{{ __('admin.developer_tools.delete_command') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="developer-command-empty">
                    <span class="developer-command-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
                    </span>
                    <p>{{ __('admin.developer_tools.commands_empty') }}</p>
                </div>
            @endforelse
            <div class="developer-command-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.3 8 10 4.6-1.7 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                <p>{{ __('admin.developer_tools.safety_note') }}</p>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script>
        document.querySelectorAll('[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.confirm)) event.preventDefault();
            });
        });
    </script>
@endpush
