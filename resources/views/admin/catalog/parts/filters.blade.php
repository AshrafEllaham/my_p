@if (!empty($filters))
    <div class="admin-catalog__filters" data-catalog-filters>
        @foreach ($filters as $filter)
            <div class="admin-filter-group" data-filter-group="{{ $filter['name'] }}">
                <span class="admin-filter-group__label">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>{{ $filter['label'] }}</span>
                </span>
                @if (($filter['type'] ?? 'tabs') === 'select')
                    <div class="admin-field__control admin-field__control--select admin-filter-select">
                        <select name="{{ $filter['name'] }}" data-filter-control aria-label="{{ $filter['label'] }}">
                            <option value="">{{ $filter['all_label'] ?? __('admin.catalog.filters.all_types') }}</option>
                            @foreach ($filter['options'] as $value => $option)
                                @php
                                    $label = is_array($option) ? ($option['label'] ?? $value) : $option;
                                @endphp
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
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                            <span>{{ $filter['all_label'] ?? __('admin.catalog.filters.all_types') }}</span>
                        </label>
                        @foreach ($filter['options'] as $value => $option)
                            @php
                                $label = is_array($option) ? ($option['label'] ?? $value) : $option;
                                $icon = is_array($option) ? ($option['icon'] ?? null) : null;
                                if ($icon === null) {
                                    $icon = match ((string)$value) {
                                        'user' => 'user',
                                        'store' => 'store',
                                        default => null,
                                    };
                                }
                            @endphp
                            <label class="admin-filter-tab {{ $activeVal === (string)$value ? 'is-active' : '' }}">
                                <input type="radio" name="{{ $filter['name'] }}" value="{{ $value }}" data-filter-control {{ $activeVal === (string)$value ? 'checked' : '' }}>
                                @if ($icon === 'user')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                @elseif ($icon === 'store')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9l1.5-5h15L21 9"/><path d="M5 9v11h14V9"/><path d="M9 20v-6h6v6"/></svg>
                                @endif
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
