<header class="admin-header">
    <div class="admin-header__start">
        <button class="admin-icon-button admin-menu-button" type="button" data-sidebar-toggle aria-label="{{ __('admin.actions.toggle_menu') }}" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <div><small>{{ __('admin.panel_name') }}</small><strong>@yield('page-title', __('admin.dashboard'))</strong></div>
    </div>
    <div class="admin-header__actions">
        <button class="admin-icon-button" type="button" aria-label="{{ __('admin.actions.notifications') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        </button>
        <div class="admin-profile">
            <span class="admin-profile__avatar">س</span>
            <span class="admin-profile__copy"><strong>{{ __('admin.app_name') }}</strong><small>{{ __('admin.panel_name') }}</small></span>
        </div>
    </div>
</header>
