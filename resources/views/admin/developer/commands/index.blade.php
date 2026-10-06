@extends('admin.layout.indexs.index')

@section('page-title', __('admin.developer_tools.commands'))

@section('content')
    @php
        $dataTableConfig = [
            'ajax' => route('admin.developer.commands.index'),
            'columns' => [
                ['data' => 'id', 'name' => 'id', 'title' => '#'],
                ['data' => 'command', 'name' => 'command', 'title' => __('admin.developer_tools.command')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('admin.catalog.fields.actions'), 'orderable' => false, 'searchable' => false],
            ],
            'language' => [
                'search' => __('admin.catalog.datatable.search'),
                'searchPlaceholder' => __('admin.catalog.datatable.search_placeholder'),
                'lengthMenu' => __('admin.catalog.datatable.length_menu'),
                'info' => __('admin.catalog.datatable.info'),
                'emptyTable' => __('admin.developer_tools.commands_empty'),
                'loadingRecords' => __('admin.catalog.datatable.processing'),
                'infoFiltered' => __('admin.developer_tools.filtered_count'),
                'aria' => [
                    'orderable' => __('admin.developer_tools.sort_column'),
                    'orderableReverse' => __('admin.developer_tools.reverse_sort'),
                    'orderableRemove' => __('admin.developer_tools.remove_sort'),
                ],
                'infoEmpty' => __('admin.catalog.datatable.info_empty'),
                'zeroRecords' => __('admin.catalog.datatable.zero_records'),
                'processing' => __('admin.catalog.datatable.processing'),
                'paginate' => [
                    'first' => __('admin.catalog.datatable.first'),
                    'last' => __('admin.catalog.datatable.last'),
                    'next' => __('admin.catalog.datatable.next'),
                    'previous' => __('admin.catalog.datatable.previous'),
                ],
            ],
        ];
    @endphp
    <section class="admin-catalog developer-commands" aria-label="{{ __('admin.developer_tools.commands') }}"
             data-catalog-page
             data-active-label="{{ __('admin.catalog.status.active') }}"
             data-inactive-label="{{ __('admin.catalog.status.inactive') }}"
             data-delete-confirm="{{ __('admin.catalog.messages.delete_confirm') }}"
             data-generic-error="{{ __('admin.catalog.messages.generic_error') }}">
        <div class="developer-page-toolbar">
            <p>{{ __('admin.developer_tools.commands_description') }}</p>
            <div class="developer-page-toolbar__actions">
                <a class="admin-button admin-button--secondary" href="{{ route('admin.developer.terminal.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m7 9 3 3-3 3M13 15h4"/></svg>
                    {{ __('admin.developer_tools.open_terminal') }}
                </a>
                <button class="admin-button admin-button--primary" type="button"
                        data-modal-url="{{ route('admin.developer.commands.create') }}"
                        data-modal-heading="{{ __('admin.developer_tools.add_command') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('admin.developer_tools.add_command') }}
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="admin-alert admin-alert--success" role="status">{{ session('success') }}</div>
        @endif

        <div class="admin-alert admin-alert--success admin-catalog__notice" data-catalog-notice hidden role="status"></div>
        <div class="admin-alert admin-alert--danger admin-catalog__notice" data-catalog-error hidden role="alert"></div>

        <div class="admin-panel admin-catalog__panel">
            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-datatable>
                    <thead>
                    <tr>
                        @foreach ($dataTableConfig['columns'] as $column)
                            <th>{{ $column['title'] }}</th>
                        @endforeach
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <p class="developer-page-note">{{ __('admin.developer_tools.safety_note') }}</p>

        <script type="application/json" data-datatable-config>{!! json_encode($dataTableConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </section>
@endsection
