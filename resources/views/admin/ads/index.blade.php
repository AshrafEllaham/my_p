@extends('admin.layout.indexs.index')

@section('page-title', $oneObjectTitle)

@section('content')
    @php
        $dataTableConfig = [
            'ajax' => route('admin.ads.index'),
            'columns' => $columns,
            'language' => [
                'search' => __('admin.catalog.datatable.search'),
                'searchPlaceholder' => __('admin.catalog.datatable.search_placeholder'),
                'lengthMenu' => __('admin.catalog.datatable.length_menu'),
                'info' => __('admin.catalog.datatable.info'),
                'infoEmpty' => __('admin.catalog.datatable.info_empty'),
                'emptyTable' => __('admin.ads.empty'),
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

    <section class="admin-catalog" aria-labelledby="ads-review-title"
             data-catalog-page
             data-generic-error="{{ __('admin.catalog.messages.generic_error') }}">
        <div class="admin-catalog__toolbar">
            <div class="admin-catalog__toolbar-title">
                <h1 id="ads-review-title">{{ $oneObjectTitle }}</h1>
                <p>{{ __('admin.ads.description') }}</p>
            </div>
            <span class="admin-status-badge admin-status-badge--pending">
                <span data-pending-count>{{ $pendingCount }}</span>
                {{ __('admin.ads.pending_count') }}
            </span>
        </div>

        <div class="admin-panel admin-catalog__panel">
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
