<aside class="admin-sidebar" data-admin-sidebar aria-label="{{ __('admin.panel_name') }}">
    <div class="admin-sidebar__brand">
        <span class="admin-sidebar__logo" aria-hidden="true">
            <svg viewBox="0 0 36 36"><path d="M8 23.5c3.5-8 8.5-12 19-12M21.5 7.5 27 11.5l-2 6.5"/></svg>
        </span>
        <span><strong>{{ __('admin.app_name') }}</strong><small>{{ __('admin.panel_name') }}</small></span>
    </div>
    <nav class="admin-nav">
        <a class="admin-nav__item {{ request()->routeIs('admin.home') ? 'is-active' : '' }}" href="{{ route('admin.home') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg>
            <span>{{ __('admin.navigation.main') }}</span>
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
        <span class="admin-nav__item is-disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-2.92 1.18V20h-2v-.48a1.7 1.7 0 0 0-2.92-1.18l-.06.06L9 16.98l.06-.06A1.7 1.7 0 0 0 7.84 14H7v-2h.84a1.7 1.7 0 0 0 1.22-2.92L9 9.02l1.42-1.42.06.06A1.7 1.7 0 0 0 13.4 6.48V6h2v.48a1.7 1.7 0 0 0 2.92 1.18l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 20.96 12H21v2h-.04A1.7 1.7 0 0 0 19.4 15Z"/></svg>
            <span>{{ __('admin.navigation.settings') }}</span>
        </span>
    </nav>
</aside>
<button class="admin-sidebar-overlay" type="button" data-sidebar-close aria-label="{{ __('admin.actions.close') }}"></button>
