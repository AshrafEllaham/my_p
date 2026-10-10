@extends('admin.layout.indexs.index')

@section('page-title', __('admin.auth.login_title'))

@section('auth-content')
    <section class="admin-login" aria-labelledby="login-title">
        <div class="admin-login__visual" aria-hidden="true">
            <div class="admin-login__grid"></div>
            <span class="admin-login__orb admin-login__orb--one"></span>
            <span class="admin-login__orb admin-login__orb--two"></span>

            <span class="admin-login__floating-icon admin-login__floating-icon--users">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <span class="admin-login__floating-icon admin-login__floating-icon--store">
                <svg viewBox="0 0 24 24"><path d="M3 9h18l-1.5-5h-15Z"/><path d="M5 9v11h14V9M9 20v-6h6v6"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg>
            </span>
            <span class="admin-login__floating-icon admin-login__floating-icon--orders">
                <svg viewBox="0 0 24 24"><path d="M6 3h12l2 5-8 4-8-4Z"/><path d="M4 8v10l8 4 8-4V8M12 12v10"/></svg>
            </span>
            <span class="admin-login__floating-icon admin-login__floating-icon--chart">
                <svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20V7"/></svg>
            </span>

            <div class="admin-login__visual-content">
                <span class="admin-login__visual-badge">
                    <x-admin.brand-mark />
                    {{ __('admin.auth.visual_eyebrow') }}
                </span>
                <h2>{{ __('admin.auth.visual_title') }}</h2>
                <p>{{ __('admin.auth.visual_description') }}</p>

                <div class="admin-login__feature-chips">
                    @foreach (__('admin.auth.features') as $feature)
                        <span>{{ $feature }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="admin-login__form-wrap">
            <x-admin.language-switch class="admin-login__locale" />

            <div class="admin-login__form-card">
                <header class="admin-login__header">
                    <span class="admin-login__logo" aria-hidden="true">
                        <x-admin.brand-mark />
                    </span>
                    <span class="admin-eyebrow">{{ __('admin.app_name') }} · {{ __('admin.panel_name') }}</span>
                    <h1 id="login-title">{{ __('admin.auth.login_title') }}</h1>
                    <p>{{ __('admin.auth.login_description') }}</p>
                </header>

                <form class="admin-login__form" method="POST" action="{{ route('admin.login.post') }}" data-admin-validate novalidate>
                    @csrf

                    <div class="admin-field">
                        <label for="email">{{ __('admin.auth.email') }}</label>
                        <div class="admin-field__control @error('email') has-error @enderror">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input id="email" name="email" type="email" maxlength="255" value="{{ old('email') }}" placeholder="admin@example.com" dir="ltr" autocomplete="username" required autofocus>
                        </div>
                        @error('email') <p class="admin-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field">
                        <label for="password">{{ __('admin.auth.password') }}</label>
                        <div class="admin-field__control @error('password') has-error @enderror">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="password" name="password" type="password" minlength="8" maxlength="255" placeholder="••••••••" dir="ltr" autocomplete="current-password" required>
                            <button class="admin-password-toggle" type="button" data-password-toggle="password" aria-label="{{ __('admin.auth.toggle_password') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="admin-field__error">{{ $message }}</p> @enderror
                    </div>

                    <label class="admin-checkbox">
                        <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                        <span>{{ __('admin.auth.remember') }}</span>
                    </label>

                    <button class="admin-login__submit" type="submit">
                        <span>{{ __('admin.auth.login_action') }}</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <p class="admin-login__security">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                    {{ __('admin.auth.secure_access') }}
                </p>

                <p class="admin-login__copyright">© {{ now()->year }} {{ __('admin.footer') }}</p>
            </div>
        </div>
    </section>
@endsection
