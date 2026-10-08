@extends('admin.layout.indexs.index')

@section('page-title', $oneObjectTitle)

@section('content')
    @php
        $dataTableConfig = [
            'ajax' => $dataUrl,
            'columns' => $columns,
            'language' => [
                'search' => __('admin.catalog.datatable.search'),
                'searchPlaceholder' => __('admin.catalog.datatable.search_placeholder'),
                'lengthMenu' => __('admin.catalog.datatable.length_menu'),
                'info' => __('admin.catalog.datatable.info'),
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
    <section class="admin-catalog" aria-labelledby="catalog-title"
             data-catalog-page
             data-active-label="{{ __('admin.catalog.status.active') }}"
             data-inactive-label="{{ __('admin.catalog.status.inactive') }}"
             data-delete-confirm="{{ __('admin.catalog.messages.delete_confirm') }}"
             data-generic-error="{{ __('admin.catalog.messages.generic_error') }}">
        <div class="admin-catalog__heading">
            <div>
                <span class="admin-eyebrow">{{ __('admin.catalog.eyebrow') }}</span>
                <h1 id="catalog-title">{{ $oneObjectTitle }}</h1>
                <p>{{ $pageDescription }}</p>
            </div>
            <button class="admin-button admin-button--primary" type="button"
                    data-modal-url="{{ $createUrl }}" data-modal-heading="{{ $createLabel }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                <span>{{ $createLabel }}</span>
            </button>
        </div>

        <div class="admin-alert admin-alert--success admin-catalog__notice" data-catalog-notice hidden role="status"></div>
        <div class="admin-alert admin-alert--danger admin-catalog__notice" data-catalog-error hidden role="alert"></div>

        <div class="admin-panel admin-catalog__panel">
            @if (!empty($filters))
                <div class="admin-catalog__filters" data-catalog-filters>
                    @foreach ($filters as $filter)
                        <div class="admin-filter-group" data-filter-group="{{ $filter['name'] }}">
                            <span class="admin-filter-group__label">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                                <span>{{ $filter['label'] }}</span>
                            </span>
                            @if (($filter['type'] ?? 'tabs') === 'select')
                                <div class="admin-field__control admin-field__control--select admin-filter-select">
                                    <select name="{{ $filter['name'] }}" data-filter-control aria-label="{{ $filter['label'] }}">
                                        <option value="">{{ $filter['all_label'] ?? __('admin.catalog.filters.all') }}</option>
                                        @foreach ($filter['options'] as $value => $label)
                                            <option value="{{ $value }}" @selected((string)($filter['value'] ?? '') === (string)$value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="admin-filter-tabs" role="radiogroup" aria-label="{{ $filter['label'] }}">
                                    @php $activeVal = (string)($filter['value'] ?? ''); @endphp
                                    <label class="admin-filter-tab {{ $activeVal === '' ? 'is-active' : '' }}">
                                        <input type="radio" name="{{ $filter['name'] }}" value="" data-filter-control {{ $activeVal === '' ? 'checked' : '' }}>
                                        <span>{{ $filter['all_label'] ?? __('admin.catalog.filters.all') }}</span>
                                    </label>
                                    @foreach ($filter['options'] as $value => $label)
                                        <label class="admin-filter-tab {{ $activeVal === (string)$value ? 'is-active' : '' }}">
                                            <input type="radio" name="{{ $filter['name'] }}" value="{{ $value }}" data-filter-control {{ $activeVal === (string)$value ? 'checked' : '' }}>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-datatable>
                    <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column['title'] }}</th>
                        @endforeach
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <script type="application/json" data-datatable-config>{!! json_encode($dataTableConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </section>
@endsection
