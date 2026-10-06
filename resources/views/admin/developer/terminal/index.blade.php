@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.terminal'))

@section('content')
    <div class="developer-page-toolbar">
        <p>{{ __('admin.developer_tools.terminal_description') }}</p>
        <a class="admin-button admin-button--secondary" href="{{ route('admin.developer.commands.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/></svg>
            {{ __('admin.developer_tools.command_list') }}
        </a>
    </div>

    <section class="developer-terminal" aria-label="{{ __('admin.developer_tools.terminal') }}">
        <div class="developer-terminal__topline">
            <div class="developer-terminal__lights" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="developer-terminal__identity">
                <strong>{{ __('admin.developer_tools.project_console') }}</strong>
                <span dir="ltr">{{ __('admin.developer_tools.artisan') }}</span>
            </div>
            <span class="developer-terminal__access"><i aria-hidden="true"></i>{{ __('admin.developer_tools.developer_access') }}</span>
        </div>

        <div class="developer-terminal__controls">
            <div class="developer-terminal__command-picker">
                <label id="developer-command-label" for="developer-command-trigger">{{ __('admin.developer_tools.command') }}</label>
                <input id="developer-command" type="hidden" value="">
                <div class="developer-command-dropdown" id="developer-command-dropdown">
                    <button id="developer-command-trigger" class="developer-command-dropdown__trigger" type="button"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="developer-command-menu"
                            aria-labelledby="developer-command-label developer-command-selection" aria-describedby="developer-command-hint">
                        <span class="developer-command-dropdown__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m7 7 5 5-5 5M14 17h4"/></svg></span>
                        <span id="developer-command-selection">{{ __('admin.developer_tools.choose_command') }}</span>
                        <svg class="developer-command-dropdown__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                    </button>
                    <div id="developer-command-menu" class="developer-command-dropdown__menu" role="dialog"
                         aria-labelledby="developer-command-label" hidden>
                        <div class="developer-command-dropdown__search">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg>
                            <input id="developer-command-search" type="search" autocomplete="off" spellcheck="false"
                                   aria-label="{{ __('admin.developer_tools.search_commands') }}"
                                   placeholder="{{ __('admin.developer_tools.search_commands') }}">
                        </div>
                        <div class="developer-command-dropdown__heading">{{ __('admin.developer_tools.command_list') }}</div>
                        <div class="developer-command-dropdown__list">
                            @foreach ($commands as $command)
                                <button class="developer-command-dropdown__option" type="button" data-command-value="{{ $command->command }}" aria-pressed="false">
                                    <span class="developer-command-dropdown__prompt" aria-hidden="true">$</span>
                                    <code dir="ltr">{{ $command->command }}</code>
                                    <svg class="developer-command-dropdown__check" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                </button>
                            @endforeach
                            <p id="developer-command-no-results" class="developer-command-dropdown__empty" role="status" hidden>{{ __('admin.developer_tools.no_matching_commands') }}</p>
                        </div>
                        <button class="developer-command-dropdown__option developer-command-dropdown__custom" type="button" data-command-value="__custom__" aria-pressed="false">
                            <span class="developer-command-dropdown__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span>
                            <span><strong>{{ __('admin.developer_tools.write_command') }}</strong><small>{{ __('admin.developer_tools.write_command_help') }}</small></span>
                            <svg class="developer-command-dropdown__check" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        </button>
                    </div>
                </div>
                <p id="developer-command-hint" class="developer-terminal__hint">{{ __('admin.developer_tools.command_picker_help') }}</p>
                <div id="developer-custom-command-field" class="developer-terminal__custom-command" hidden>
                    <label for="developer-custom-command">{{ __('admin.developer_tools.custom_command') }}</label>
                    <div class="admin-field__control developer-terminal__input">
                        <span aria-hidden="true">$</span>
                        <input id="developer-custom-command" dir="ltr" spellcheck="false" type="text" maxlength="255"
                               autocomplete="off" disabled placeholder="{{ __('admin.developer_tools.command_placeholder') }}"
                               aria-describedby="developer-custom-command-hint">
                    </div>
                    <p id="developer-custom-command-hint" class="developer-terminal__hint">{{ __('admin.developer_tools.command_format_help') }}</p>
                </div>
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
            const picker = document.getElementById('developer-command');
            const dropdown = document.getElementById('developer-command-dropdown');
            const trigger = document.getElementById('developer-command-trigger');
            const selection = document.getElementById('developer-command-selection');
            const menu = document.getElementById('developer-command-menu');
            const search = document.getElementById('developer-command-search');
            const noResults = document.getElementById('developer-command-no-results');
            const options = Array.from(menu.querySelectorAll('[data-command-value]'));
            const command = document.getElementById('developer-custom-command');
            const customField = document.getElementById('developer-custom-command-field');
            const runButton = document.getElementById('run-command');
            const clearButton = document.getElementById('clear-output');
            const output = document.getElementById('command-output');
            const status = document.getElementById('command-status');

            let running = false;

            const isCustomCommand = () => picker.value === '__custom__';
            const selectedCommandValue = () => isCustomCommand() ? command.value.trim() : picker.value;
            const updateRunButton = () => {
                runButton.disabled = running || !selectedCommandValue();
            };

            const filterCommands = () => {
                const query = search.value.trim().toLowerCase();
                let matches = 0;
                options.forEach((option) => {
                    if (option.dataset.commandValue === '__custom__') return;
                    option.hidden = !option.dataset.commandValue.toLowerCase().includes(query);
                    if (!option.hidden) matches++;
                });
                noResults.hidden = matches > 0;
            };
            const closeMenu = (restoreFocus = false) => {
                menu.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                if (restoreFocus) trigger.focus();
            };
            const openMenu = () => {
                if (running) return;
                menu.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                search.value = '';
                filterCommands();
                search.focus();
            };
            trigger.addEventListener('click', () => menu.hidden ? openMenu() : closeMenu(true));
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    openMenu();
                }
            });
            search.addEventListener('input', filterCommands);
            options.forEach((option) => {
                option.addEventListener('click', () => {
                    if (running) return;
                    picker.value = option.dataset.commandValue;
                    selection.textContent = isCustomCommand()
                        ? @json(__('admin.developer_tools.write_command'))
                        : picker.value;
                    selection.dir = isCustomCommand() ? document.documentElement.dir : 'ltr';
                    selection.classList.toggle('is-command', !isCustomCommand());
                    options.forEach((item) => item.setAttribute('aria-pressed', String(item === option)));
                    closeMenu(true);
                    picker.dispatchEvent(new Event('change'));
                });
            });
            menu.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    closeMenu(true);
                    return;
                }
                if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
                event.preventDefault();
                const visible = options.filter((option) => !option.hidden);
                const index = visible.indexOf(document.activeElement);
                const next = event.key === 'ArrowDown'
                    ? (index + 1) % visible.length
                    : (index <= 0 ? visible.length - 1 : index - 1);
                visible[next]?.focus();
            });
            document.addEventListener('click', (event) => {
                if (!dropdown.contains(event.target)) closeMenu();
            });
            dropdown.addEventListener('focusout', (event) => {
                if (!dropdown.contains(event.relatedTarget)) closeMenu();
            });

            picker.addEventListener('change', () => {
                const custom = isCustomCommand();
                customField.hidden = !custom;
                command.disabled = !custom;
                picker.dir = picker.value && !custom ? 'ltr' : document.documentElement.dir;
                status.textContent = '';
                updateRunButton();
                if (custom) command.focus();
            });

            command.addEventListener('input', () => {
                updateRunButton();
                status.textContent = '';
            });

            clearButton.addEventListener('click', () => {
                output.textContent = '';
                status.textContent = '';
            });

            command.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    runButton.click();
                }
            });

            runButton.addEventListener('click', async () => {
                if (running || !selectedCommandValue() || runButton.disabled) return;

                const selectedCommand = selectedCommandValue();
                closeMenu();
                trigger.disabled = true;
                picker.disabled = true;
                running = true;
                command.readOnly = true;
                clearButton.disabled = true;
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
                    running = false;
                    command.readOnly = false;
                    clearButton.disabled = false;
                    trigger.disabled = false;
                    picker.disabled = false;
                    updateRunButton();
                    (isCustomCommand() ? command : trigger).focus();
                    output.scrollTop = output.scrollHeight;
                }
            });
        })();
    </script>
@endpush
