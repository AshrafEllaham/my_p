<header class="admin-header">
    <div class="admin-header__start">
        <button class="admin-icon-button admin-menu-button" type="button" data-sidebar-toggle aria-label="{{ __('admin.actions.toggle_menu') }}" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <a class="admin-header__brand" href="{{ route('admin.index') }}" aria-label="{{ __('admin.app_name') }} — {{ __('admin.panel_name') }}">
            <span class="admin-header__brand-mark" aria-hidden="true">
                <x-admin.brand-mark />
            </span>
            <span class="admin-header__brand-copy">
                <strong>{{ __('admin.app_name') }}</strong>
                <small>{{ __('admin.panel_name') }}</small>
            </span>
        </a>
    </div>
    <div class="admin-header__actions">
        <button class="admin-settings-menu__trigger" type="button" data-admin-settings aria-label="{{ __('admin.navigation.settings') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-2.92 1.18V20h-2v-.48a1.7 1.7 0 0 0-2.92-1.18l-.06.06L9 16.98l.06-.06A1.7 1.7 0 0 0 7.84 14H7v-2h.84a1.7 1.7 0 0 0 1.22-2.92L9 9.02l1.42-1.42.06.06A1.7 1.7 0 0 0 13.4 6.48V6h2v.48a1.7 1.7 0 0 0 2.92 1.18l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 20.96 12H21v2h-.04A1.7 1.7 0 0 0 19.4 15Z"/></svg>
            <span>{{ __('admin.navigation.settings') }}</span>
        </button>
        <details class="admin-theme-menu" data-theme-menu>
            <summary class="admin-icon-button admin-theme-menu__trigger" title="{{ __('admin.theme.title') }}" aria-label="{{ __('admin.theme.title') }}">
                <svg class="admin-theme-menu__icon admin-theme-menu__icon--light" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"/></svg>
                <svg class="admin-theme-menu__icon admin-theme-menu__icon--dark" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>
            </summary>
            <div class="admin-theme-menu__dropdown" role="radiogroup" aria-label="{{ __('admin.theme.title') }}">
                <span class="admin-theme-menu__title">{{ __('admin.theme.title') }}</span>
                <button class="admin-theme-menu__option" type="button" role="radio" aria-label="{{ __('admin.theme.light') }}" aria-checked="false" data-theme-option="light">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"/></svg>
                    <span>{{ __('admin.theme.light') }}</span>
                    <i aria-hidden="true"></i>
                </button>
                <button class="admin-theme-menu__option" type="button" role="radio" aria-label="{{ __('admin.theme.dark') }}" aria-checked="false" data-theme-option="dark">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>
                    <span>{{ __('admin.theme.dark') }}</span>
                    <i aria-hidden="true"></i>
                </button>
                <button class="admin-theme-menu__option" type="button" role="radio" aria-label="{{ __('admin.theme.system') }}" aria-checked="false" data-theme-option="system">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg>
                    <span>{{ __('admin.theme.system') }}</span>
                    <i aria-hidden="true"></i>
                </button>
            </div>
        </details>
        <x-admin.language-switch class="admin-header__locale" />
        <button class="admin-icon-button" type="button" aria-label="{{ __('admin.actions.notifications') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        </button>
        <details class="admin-account-menu">
            <summary class="admin-profile" aria-label="{{ __('admin.profile.open_menu') }}">
                <span class="admin-profile__avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                </span>
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
