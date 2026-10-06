@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.terminal'))

@section('content')
    <section class="admin-hero developer-page-hero">
        <div>
            <span class="admin-eyebrow">{{ __('admin.developer_tools.eyebrow') }}</span>
            <h1>{{ __('admin.developer_tools.terminal') }}</h1>
            <p>{{ __('admin.developer_tools.terminal_description') }}</p>
        </div>
        <a class="admin-button admin-button--secondary" href="{{ route('admin.developer.commands.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
            {{ __('admin.developer_tools.command_list') }}
        </a>
    </section>

    <section class="developer-terminal" aria-label="{{ __('admin.developer_tools.terminal') }}">
        <div class="developer-terminal__topline">
            <div class="developer-terminal__lights" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="developer-terminal__identity">
                <strong>{{ __('admin.developer_tools.project_console') }}</strong>
                <span dir="ltr">artisan</span>
            </div>
            <span class="developer-terminal__access"><i aria-hidden="true"></i>{{ __('admin.developer_tools.developer_access') }}</span>
        </div>

        <div class="developer-terminal__controls">
            <div class="developer-terminal__control-heading">
                <div>
                    <span class="developer-terminal__eyebrow">{{ __('admin.developer_tools.execution_workspace') }}</span>
                    <h2>{{ __('admin.developer_tools.select_command') }}</h2>
                </div>
                <span class="developer-terminal__command-count">{{ __('admin.developer_tools.command_count', ['count' => $commands->count()]) }}</span>
            </div>
            <div class="developer-terminal__command-picker">
                <label for="developer-command">{{ __('admin.developer_tools.command') }}</label>
                <input id="developer-command" class="admin-field__control" type="text" list="developer-command-options" maxlength="255" autocomplete="off" placeholder="{{ __('admin.developer_tools.command_placeholder') }}" aria-describedby="developer-command-hint">
                <datalist id="developer-command-options">
                    @foreach ($commands as $command)
                        <option value="{{ $command->command }}"></option>
                    @endforeach
                </datalist>
                <p id="developer-command-hint" class="developer-terminal__hint">{{ __('admin.developer_tools.command_format_help') }}</p>
            </div>
            <div class="developer-terminal__actions">
                <button id="run-command" type="button" class="admin-button admin-button--primary" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7V5Z"/></svg>
                    {{ __('admin.developer_tools.run_command') }}
                </button>
                <button id="clear-output" type="button" class="admin-button admin-button--secondary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                    {{ __('admin.developer_tools.clear_output') }}
                </button>
                <span id="command-status" role="status" aria-live="polite">{{ __('admin.developer_tools.awaiting_execution') }}</span>
            </div>
        </div>

        <div class="developer-terminal__output-wrap">
            <div class="developer-terminal__output-heading">
                <span>{{ __('admin.developer_tools.output') }}</span>
                <span class="developer-terminal__output-state"><i aria-hidden="true"></i>{{ __('admin.developer_tools.console_ready') }}</span>
            </div>
            <pre id="command-output" class="developer-terminal__output" dir="ltr" aria-live="polite">{{ __('admin.developer_tools.terminal_ready') }}</pre>
        </div>
        <p class="developer-terminal__note">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.3 8 10 4.6-1.7 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
            {{ __('admin.developer_tools.safety_note') }}
        </p>
    </section>
@endsection

@push('js')
    <script>
        (() => {
            const command = document.getElementById('developer-command');
            const runButton = document.getElementById('run-command');
            const clearButton = document.getElementById('clear-output');
            const output = document.getElementById('command-output');
            const status = document.getElementById('command-status');

            command.addEventListener('input', () => {
                runButton.disabled = !command.value.trim();
                status.textContent = '';
            });

            clearButton.addEventListener('click', () => {
                output.textContent = '';
                status.textContent = '';
            });

            runButton.addEventListener('click', async () => {
                if (!command.value.trim() || runButton.disabled) return;

                const selectedCommand = command.value.trim();
                runButton.disabled = true;
                status.textContent = @json(__('admin.developer_tools.running'));
                output.textContent += `\n$ ${selectedCommand}\n`;

                try {
                    const response = await fetch(@json(route('admin.developer.terminal.run')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({command: selectedCommand}),
                    });
                    const result = await response.json();
                    output.textContent += `${result.output ?? result.errors?.command?.[0] ?? @json(__('admin.developer_tools.command_failed'))}\n`;
                    status.textContent = response.ok
                        ? @json(__('admin.developer_tools.completed'))
                        : @json(__('admin.developer_tools.failed'));
                } catch (error) {
                    output.textContent += `${@json(__('admin.developer_tools.request_failed'))}\n`;
                    status.textContent = @json(__('admin.developer_tools.failed'));
                } finally {
                    command.value = '';
                    runButton.disabled = true;
                    output.scrollTop = output.scrollHeight;
                }
            });
        })();
    </script>
@endpush
