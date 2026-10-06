@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.terminal'))

@section('content')
    <section class="admin-hero">
        <div>
            <span class="admin-eyebrow">{{ __('admin.developer_tools.eyebrow') }}</span>
            <h1>{{ __('admin.developer_tools.terminal') }}</h1>
            <p>{{ __('admin.developer_tools.terminal_description') }}</p>
        </div>
        <a class="admin-button admin-button--secondary" href="{{ route('admin.developer.commands.index') }}">
            {{ __('admin.developer_tools.command_list') }}
        </a>
    </section>

    <section class="admin-panel developer-terminal" aria-label="{{ __('admin.developer_tools.terminal') }}">
        <div class="developer-terminal__topline">
            <div class="developer-terminal__lights" aria-hidden="true"><i></i><i></i><i></i></div>
            <span>{{ __('admin.developer_tools.project_console') }}</span>
            <span class="developer-terminal__access">{{ __('admin.developer_tools.developer_access') }}</span>
        </div>

        <div class="developer-terminal__controls">
            <label for="developer-command">{{ __('admin.developer_tools.select_command') }}</label>
            <select id="developer-command" class="admin-field__control">
                <option value="">{{ __('admin.developer_tools.choose_command') }}</option>
                @foreach ($commands as $command)
                    <option value="{{ $command->command }}">{{ $command->command }}</option>
                @endforeach
            </select>
            <div class="developer-terminal__actions">
                <button id="run-command" type="button" class="admin-button admin-button--primary" disabled>
                    {{ __('admin.developer_tools.run_command') }}
                </button>
                <button id="clear-output" type="button" class="admin-button admin-button--secondary">
                    {{ __('admin.developer_tools.clear_output') }}
                </button>
                <span id="command-status" role="status" aria-live="polite"></span>
            </div>
        </div>

        <pre id="command-output" class="developer-terminal__output" dir="ltr" aria-live="polite">{{ __('admin.developer_tools.terminal_ready') }}</pre>
        <p class="developer-terminal__note">{{ __('admin.developer_tools.safety_note') }}</p>
    </section>
@endsection

@push('css')
    <style>
        .developer-terminal { overflow: hidden; padding: 0; background: #111827; color: #e5e7eb; }
        .developer-terminal__topline { display: flex; align-items: center; gap: 12px; min-height: 48px; padding: 0 18px; border-bottom: 1px solid #263244; color: #9ca3af; font-size: 11px; }
        .developer-terminal__lights { display: flex; gap: 6px; }
        .developer-terminal__lights i { width: 8px; height: 8px; border-radius: 50%; background: #f87171; }
        .developer-terminal__lights i:nth-child(2) { background: #fbbf24; }
        .developer-terminal__lights i:nth-child(3) { background: #34d399; }
        .developer-terminal__access { margin-inline-start: auto; }
        .developer-terminal__controls { padding: 20px; }
        .developer-terminal__controls label { display: block; margin-bottom: 8px; color: #d1d5db; font-size: 12px; font-weight: 800; }
        .developer-terminal__controls select { background: #0b1220; color: #f9fafb; }
        .developer-terminal__actions { display: flex; align-items: center; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        .developer-terminal__actions .admin-button--secondary { color: #e5e7eb; border-color: #374151; background: #1f2937; }
        .developer-terminal__actions button:disabled { cursor: not-allowed; opacity: .5; }
        #command-status { color: #9ca3af; font-size: 12px; }
        .developer-terminal__output { min-height: 280px; max-height: 520px; overflow: auto; margin: 0 20px; padding: 18px; border: 1px solid #263244; border-radius: 10px; background: #080d16; color: #d1fae5; white-space: pre-wrap; overflow-wrap: anywhere; font: 12px/1.7 ui-monospace, SFMono-Regular, Menlo, monospace; }
        .developer-terminal__note { margin: 12px 20px 20px; color: #9ca3af; font-size: 11px; line-height: 1.6; }
        html[dir="rtl"] .developer-terminal__output { text-align: left; }
        @media (max-width: 600px) { .developer-terminal__output { min-height: 220px; margin-inline: 12px; } .developer-terminal__controls { padding: 14px; } .developer-terminal__note { margin-inline: 12px; } }
    </style>
@endpush

@push('js')
    <script>
        (() => {
            const command = document.getElementById('developer-command');
            const runButton = document.getElementById('run-command');
            const clearButton = document.getElementById('clear-output');
            const output = document.getElementById('command-output');
            const status = document.getElementById('command-status');

            command.addEventListener('change', () => {
                runButton.disabled = !command.value;
                status.textContent = '';
            });

            clearButton.addEventListener('click', () => {
                output.textContent = '';
                status.textContent = '';
            });

            runButton.addEventListener('click', async () => {
                if (!command.value || runButton.disabled) return;

                const selectedCommand = command.value;
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
