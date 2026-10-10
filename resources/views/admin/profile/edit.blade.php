@extends('admin.layout.indexs.index')

@section('page-title', $oneObjectTitle)

@section('content')
    <section class="admin-profile-page" aria-labelledby="profile-title">
        <aside class="admin-profile-card">
            <span class="admin-profile-card__avatar" aria-hidden="true">{{ mb_substr($admin->name, 0, 1) }}</span>
            <div>
                <span class="admin-eyebrow">{{ __('admin.profile.account') }}</span>
                <h1 id="profile-title">{{ $admin->name }}</h1>
                <p>{{ $admin->email }}</p>
            </div>
            <span class="admin-profile-card__role">{{ $admin->admin_type?->label() ?? __('admin.auth.admin_role') }}</span>
        </aside>

        <div class="admin-profile-form-card">
            <div class="admin-section__heading admin-profile-form-card__heading">
                <div>
                    <span class="admin-eyebrow">{{ __('admin.profile.details_eyebrow') }}</span>
                    <h2>{{ __('admin.profile.details_title') }}</h2>
                    <p>{{ __('admin.profile.details_description') }}</p>
                </div>
            </div>

            @if (session('success'))
                <div class="admin-alert admin-alert--success" role="status">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form class="admin-profile-form" method="POST" action="{{ route('admin.profile.update') }}" data-admin-validate>
                @csrf
                @method('PUT')

                <div class="admin-profile-form__grid">
                    <div class="admin-field">
                        <label for="name">{{ __('admin.profile.name') }}</label>
                        <div class="admin-field__control @error('name') has-error @enderror">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            <input id="name" name="name" type="text" maxlength="255" value="{{ old('name', $admin->name) }}" autocomplete="name" required>
                        </div>
                        @error('name')<p class="admin-field__error">{{ $message }}</p>@enderror
                    </div>

                    <div class="admin-field">
                        <label for="email">{{ __('admin.profile.email') }}</label>
                        <div class="admin-field__control @error('email') has-error @enderror">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $admin->email) }}" autocomplete="email" dir="ltr" required>
                        </div>
                        @error('email')<p class="admin-field__error">{{ $message }}</p>@enderror
                    </div>

                    <div class="admin-field admin-profile-form__full">
                        <label for="phone">{{ __('admin.profile.phone') }}</label>
                        <div class="admin-field__control @error('phone') has-error @enderror">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.69 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.33 1.85.56 2.81.69A2 2 0 0 1 22 16.92Z"/></svg>
                            <input id="phone" name="phone" type="tel" maxlength="32" value="{{ old('phone', $admin->phone) }}" autocomplete="tel" dir="ltr">
                        </div>
                        @error('phone')<p class="admin-field__error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="admin-profile-form__security">
                    <div class="admin-profile-form__section-title">
                        <span class="admin-profile-form__section-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                        <div><h3>{{ __('admin.profile.security_title') }}</h3><p>{{ __('admin.profile.security_description') }}</p></div>
                    </div>

                    <div class="admin-profile-form__grid">
                        <div class="admin-field admin-profile-form__full">
                            <label for="current_password">{{ __('admin.profile.current_password') }}</label>
                            <div class="admin-field__control @error('current_password') has-error @enderror">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                <input id="current_password" name="current_password" type="password" autocomplete="current-password">
                                <button class="admin-password-toggle" type="button" data-password-toggle="current_password" aria-label="{{ __('admin.auth.toggle_password') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                            @error('current_password')<p class="admin-field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="admin-field">
                            <label for="password">{{ __('admin.profile.new_password') }}</label>
                            <div class="admin-field__control @error('password') has-error @enderror">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                <input id="password" name="password" type="password" minlength="8" maxlength="255" autocomplete="new-password">
                                <button class="admin-password-toggle" type="button" data-password-toggle="password" aria-label="{{ __('admin.auth.toggle_password') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                            @error('password')<p class="admin-field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="admin-field">
                            <label for="password_confirmation">{{ __('admin.profile.password_confirmation') }}</label>
                            <div class="admin-field__control">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="255" autocomplete="new-password">
                                <button class="admin-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="{{ __('admin.auth.toggle_password') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-profile-form__actions">
                    <a class="admin-button admin-button--secondary" href="{{ route('admin.index') }}">{{ __('admin.actions.cancel') }}</a>
                    <button class="admin-button admin-button--primary" type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span>{{ __('admin.profile.save') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
