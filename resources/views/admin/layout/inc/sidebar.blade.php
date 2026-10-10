<aside class="admin-sidebar" data-admin-sidebar aria-label="{{ __('admin.panel_name') }}">
    <a class="admin-sidebar__brand" href="{{ route('admin.index') }}" aria-label="{{ __('admin.app_name') }} — {{ __('admin.panel_name') }}">
        <span class="admin-sidebar__logo" aria-hidden="true">
            <x-admin.brand-mark />
        </span>
        <span class="admin-sidebar__brand-copy">
            <strong>{{ __('admin.app_name') }}</strong>
            <small>{{ __('admin.panel_name') }}</small>
        </span>
    </a>
    <nav class="admin-nav">
        <a class="admin-nav__item {{ request()->routeIs('admin.index') ? 'is-active' : '' }}" href="{{ route('admin.index') }}" @if(request()->routeIs('admin.index')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg>
            <span>{{ __('admin.navigation.main') }}</span>
        </a>
        @php
            $catalogNavigationIsActive = request()->routeIs([
                'admin.countries.*',
                'admin.governorates.*',
                'admin.cities.*',
                'admin.main-categories.*',
                'admin.sub-categories.*',
            ]);
        @endphp
        <details class="admin-nav-group {{ $catalogNavigationIsActive ? 'is-active' : '' }}" data-catalog-navigation @if($catalogNavigationIsActive) open @endif>
            <summary class="admin-nav__item admin-nav-group__trigger">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                <span>{{ __('admin.navigation.catalog') }}</span>
                <svg class="admin-nav-group__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
            </summary>
            <div class="admin-nav-group__menu">
                <a class="admin-nav__subitem {{ request()->routeIs('admin.countries.*') ? 'is-active' : '' }}" href="{{ route('admin.countries.index') }}" @if(request()->routeIs('admin.countries.*')) aria-current="page" @endif>
                    <span class="admin-nav__subitem-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3.5 9h17M3.5 15h17M12 3c2.3 2.5 3.5 5.5 3.5 9S14.3 18.5 12 21c-2.3-2.5-3.5-5.5-3.5-9S9.7 5.5 12 3Z"/></svg>
                    </span>
                    <span>{{ __('admin.navigation.countries') }}</span>
                </a>
                <a class="admin-nav__subitem {{ request()->routeIs('admin.governorates.*') ? 'is-active' : '' }}" href="{{ route('admin.governorates.index') }}" @if(request()->routeIs('admin.governorates.*')) aria-current="page" @endif>
                    <span class="admin-nav__subitem-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>
                    </span>
                    <span>{{ __('admin.navigation.governorates') }}</span>
                </a>
                <a class="admin-nav__subitem {{ request()->routeIs('admin.cities.*') ? 'is-active' : '' }}" href="{{ route('admin.cities.index') }}" @if(request()->routeIs('admin.cities.*')) aria-current="page" @endif>
                    <span class="admin-nav__subitem-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 21V9l6-3v15M10 21V3l10 4v14M2 21h20M14 9h2M14 13h2M14 17h2M6 12h1M6 16h1"/></svg>
                    </span>
                    <span>{{ __('admin.navigation.cities') }}</span>
                </a>
                <a class="admin-nav__subitem {{ request()->routeIs('admin.main-categories.*') ? 'is-active' : '' }}" href="{{ route('admin.main-categories.index') }}" @if(request()->routeIs('admin.main-categories.*')) aria-current="page" @endif>
                    <span class="admin-nav__subitem-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg>
                    </span>
                    <span>{{ __('admin.navigation.main_categories') }}</span>
                </a>
                <a class="admin-nav__subitem {{ request()->routeIs('admin.sub-categories.*') ? 'is-active' : '' }}" href="{{ route('admin.sub-categories.index') }}" @if(request()->routeIs('admin.sub-categories.*')) aria-current="page" @endif>
                    <span class="admin-nav__subitem-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 5h6l2 3h8v11H4Z"/><path d="M8 12h8M8 16h5"/></svg>
                    </span>
                    <span>{{ __('admin.navigation.sub_categories') }}</span>
                </a>
            </div>
        </details>
        <a class="admin-nav__item {{ request()->routeIs('admin.banners.*') ? 'is-active' : '' }}" href="{{ route('admin.banners.index') }}" @if(request()->routeIs('admin.banners.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg>
            <span>{{ __('admin.navigation.banners') }}</span>
        </a>
        <a class="admin-nav__item {{ request()->routeIs('admin.ad-packages.*') ? 'is-active' : '' }}" href="{{ route('admin.ad-packages.index') }}" @if(request()->routeIs('admin.ad-packages.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/><path d="m15 17 1.5 1.5L20 15"/></svg>
            <span>{{ __('admin.navigation.ad_packages') }}</span>
        </a>
        <a class="admin-nav__item {{ request()->routeIs('admin.ads.*') ? 'is-active' : '' }}" href="{{ route('admin.ads.index') }}" @if(request()->routeIs('admin.ads.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 9h8M8 13h5M16 16l2 2 4-4"/></svg>
            <span>{{ __('admin.navigation.ads_review') }}</span>
        </a>
        <a class="admin-nav__item {{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}" href="{{ route('admin.faqs.index') }}" @if(request()->routeIs('admin.faqs.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 0 1 4.8 1c0 1.7-2.4 2-2.4 4M12 17.5h.01"/></svg>
            <span>{{ __('admin.navigation.faqs') }}</span>
        </a>
        <a class="admin-nav__item {{ request()->routeIs('admin.contact-us.*') ? 'is-active' : '' }}" href="{{ route('admin.contact-us.index') }}" @if(request()->routeIs('admin.contact-us.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v12H7l-3 3Z"/><path d="M8 9h8M8 13h5"/></svg>
            <span>{{ __('admin.navigation.contact_us') }}</span>
        </a>
        <a class="admin-nav__item {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}" @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 0 0-1.75.5l-.06.06h-3.4l-.02-.08a1.7 1.7 0 0 0-1.34-1.2 1.7 1.7 0 0 0-1.56.5l-.06.06-2.94-1.7.02-.08a1.7 1.7 0 0 0-.5-1.75l-.06-.06v-3.4l.08-.02a1.7 1.7 0 0 0 1.2-1.34 1.7 1.7 0 0 0-.5-1.56l-.06-.06 1.7-2.94.08.02a1.7 1.7 0 0 0 1.75-.5l.06-.06h3.4l.02.08a1.7 1.7 0 0 0 1.34 1.2 1.7 1.7 0 0 0 1.56-.5l.06-.06 2.94 1.7-.02.08a1.7 1.7 0 0 0 .5 1.75l.06.06v3.4z"/></svg>
            <span>{{ __('admin.navigation.settings') }}</span>
        </a>
        <span class="admin-nav__item is-disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>{{ __('admin.navigation.users') }}</span>
        </span>
        <span class="admin-nav__item is-disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9h18l-1.5-5h-15Z"/><path d="M5 9v11h14V9M9 20v-6h6v6"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg>
            <span>{{ __('admin.navigation.stores') }}</span>
        </span>
        <span class="admin-nav__item is-disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12l2 5-8 4-8-4Z"/><path d="M4 8v10l8 4 8-4V8M12 12v10"/></svg>
            <span>{{ __('admin.navigation.orders') }}</span>
        </span>
        @if (auth('admin')->user()?->admin_type === \App\Enums\AdminTypeEnum::Developer)
            <details class="admin-nav-group {{ request()->routeIs('admin.developer.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.developer.*')) open @endif>
                <summary class="admin-nav__item admin-nav-group__trigger">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 7-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/></svg>
                    <span>{{ __('admin.navigation.developer_tools') }}</span>
                    <svg class="admin-nav-group__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </summary>
                <div class="admin-nav-group__menu">
                    <a class="admin-nav__subitem {{ request()->routeIs('admin.developer.commands.*') ? 'is-active' : '' }}" href="{{ route('admin.developer.commands.index') }}" @if (request()->routeIs('admin.developer.commands.*')) aria-current="page" @endif>
                        <span>{{ __('admin.developer_tools.commands') }}</span>
                    </a>
                    <a class="admin-nav__subitem {{ request()->routeIs('admin.developer.terminal.*') ? 'is-active' : '' }}" href="{{ route('admin.developer.terminal.index') }}" @if (request()->routeIs('admin.developer.terminal.*')) aria-current="page" @endif>
                        <span>{{ __('admin.developer_tools.terminal') }}</span>
                    </a>
                    <a class="admin-nav__subitem" href="{{ url(config('log-viewer.route_path', 'log-viewer')) }}" target="_blank" rel="noopener">
                        <span>{{ __('admin.developer_tools.log_viewer') }}</span>
                    </a>
                </div>
            </details>
        @endif
    </nav>
</aside>
<button class="admin-sidebar-overlay" type="button" data-sidebar-close aria-label="{{ __('admin.actions.close') }}"></button>
