<aside class="admin-sidebar" data-admin-sidebar aria-label="{{ __('admin.panel_name') }}">
    <nav class="admin-nav">
        <a class="admin-nav__item {{ request()->routeIs('admin.index') ? 'is-active' : '' }}" href="{{ route('admin.index') }}" @if(request()->routeIs('admin.index')) aria-current="page" @endif>
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
    </nav>
</aside>
<button class="admin-sidebar-overlay" type="button" data-sidebar-close aria-label="{{ __('admin.actions.close') }}"></button>
