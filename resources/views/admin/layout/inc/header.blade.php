<header class="admin-header">
    <div class="admin-header__start">
        <button class="admin-icon-button admin-menu-button" type="button" data-sidebar-toggle aria-label="{{ __('admin.actions.toggle_menu') }}" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <div><small>{{ __('admin.panel_name') }}</small><strong>@yield('page-title', __('admin.dashboard'))</strong></div>
    </div>
    <div class="admin-header__actions">
        <x-admin.language-switch class="admin-header__locale" />
        <button class="admin-icon-button" type="button" aria-label="{{ __('admin.actions.notifications') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        </button>
        <details class="admin-account-menu">
            <summary class="admin-profile" aria-label="{{ __('admin.profile.open_menu') }}">
                <span class="admin-profile__avatar">{{ mb_substr(auth('admin')->user()->name, 0, 1) }}</span>
                <span class="admin-profile__copy"><strong>{{ auth('admin')->user()->name }}</strong><small>{{ auth('admin')->user()->admin_type?->label() ?? __('admin.auth.admin_role') }}</small></span>
                <svg class="admin-profile__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 10 3 3 3-3"/></svg>
            </summary>
            <div class="admin-account-menu__dropdown">
                <div class="admin-account-menu__identity">
                    <strong>{{ auth('admin')->user()->name }}</strong>
                    <small dir="ltr">{{ auth('admin')->user()->email }}</small>
                </div>
                <a class="admin-account-menu__item" href="{{ route('admin.profile.edit') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    <span>{{ __('admin.profile.title') }}</span>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="admin-account-menu__item admin-account-menu__item--danger" type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>
                        <span>{{ __('admin.auth.logout') }}</span>
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>
