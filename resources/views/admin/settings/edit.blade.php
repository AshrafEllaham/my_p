@extends('admin.layout.indexs.index')

@section('page-title', $oneObjectTitle)

@section('content')
    @php
        $arabic = $settings?->translations->firstWhere('locale', 'ar');
        $english = $settings?->translations->firstWhere('locale', 'en');
        $imageFields = ['fav_icon', 'logo_header', 'logo_footer'];
    @endphp

    <section class="admin-settings-page" aria-labelledby="settings-title">
        <div class="admin-catalog__heading">
            <div>
                <span class="admin-eyebrow">{{ __('admin.navigation.settings') }}</span>
                <h1 id="settings-title">{{ $oneObjectTitle }}</h1>
                <p>{{ __('admin.site_settings.description') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="admin-alert admin-alert--success" role="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="admin-panel admin-settings-section" aria-labelledby="settings-identity-title">
                <div class="admin-section__heading">
                    <div>
                        <h2 id="settings-identity-title">{{ __('admin.site_settings.identity_title') }}</h2>
                    </div>
                </div>
                <div class="admin-settings-images-grid">
                    @foreach ($imageFields as $field)
                        <div class="admin-settings-image-card">
                            <div class="admin-settings-image-card__header">
                                <label class="admin-settings-image-card__title" for="{{ $field }}">
                                    @if ($field === 'fav_icon')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    @elseif ($field === 'logo_header')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15h18"/></svg>
                                    @endif
                                    <span>{{ __('admin.site_settings.fields.'.$field) }}</span>
                                </label>
                                @if (!empty($imageUrls[$field]))
                                    <span class="admin-settings-image-card__badge">{{ __('admin.site_settings.current_image') }}</span>
                                @else
                                    <span class="admin-settings-image-card__badge admin-settings-image-card__badge--empty">{{ __('admin.site_settings.no_image') }}</span>
                                @endif
                            </div>
                            <input
                                class="dropify @error($field) has-error @enderror"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                data-max-file-size="5M"
                                data-height="180"
                                data-default-file="{{ $imageUrls[$field] }}"
                                data-dropify-default="{{ __('admin.site_settings.drop_file') }}"
                                data-dropify-replace="{{ __('admin.site_settings.replace_file') }}"
                                data-dropify-remove="{{ __('admin.site_settings.clear_file') }}"
                                data-dropify-error="{{ __('admin.site_settings.dropify_error') }}"
                                data-dropify-file-size-error="{{ __('admin.site_settings.file_size_error') }}"
                                data-dropify-file-type-error="{{ __('admin.site_settings.file_type_error') }}"
                                aria-describedby="{{ $field }}-help"
                            >
                            <small id="{{ $field }}-help" class="admin-field__help">
                                {{ __('admin.site_settings.'.$field.'_help') }}
                            </small>
                            @error($field)<p class="admin-field__error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel admin-settings-section" aria-labelledby="settings-contact-title">
                <div class="admin-section__heading">
                    <div><h2 id="settings-contact-title">{{ __('admin.site_settings.contact_title') }}</h2></div>
                </div>
                <div class="admin-form-grid">
                    @foreach (['whatsapp', 'phone', 'other_phone', 'email', 'facebook', 'instagram'] as $field)
                        <div class="admin-field">
                            <label for="{{ $field }}">{{ __('admin.site_settings.fields.'.$field) }}</label>
                            <input
                                class="admin-input @error($field) has-error @enderror"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                type="{{ $field === 'email' ? 'email' : (in_array($field, ['facebook', 'instagram'], true) ? 'url' : 'text') }}"
                                value="{{ old($field, $settings?->{$field}) }}"
                                dir="{{ in_array($field, ['email', 'facebook', 'instagram', 'whatsapp', 'phone'], true) ? 'ltr' : 'auto' }}"
                            >
                            @error($field)<p class="admin-field__error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel admin-settings-section" aria-labelledby="settings-content-title">
                <div class="admin-section__heading">
                    <div>
                        <h2 id="settings-content-title">{{ __('admin.site_settings.content_title') }}</h2>
                    </div>
                </div>
                <div class="admin-settings-languages">
                    @foreach (['ar' => $arabic, 'en' => $english] as $locale => $translation)
                        <fieldset class="admin-settings-language" lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
                            <legend>{{ __('admin.site_settings.languages.'.$locale) }}</legend>
                            @foreach (['website_name', 'about_app', 'privacy', 'terms_conditions'] as $field)
                                <div class="admin-field">
                                    <label for="{{ $locale }}-{{ $field }}">{{ __('admin.site_settings.fields.'.$field) }}</label>
                                    @if ($field === 'website_name')
                                        <input
                                            class="admin-input @error($locale.'.'.$field) has-error @enderror"
                                            id="{{ $locale }}-{{ $field }}"
                                            name="{{ $locale }}[{{ $field }}]"
                                            type="text"
                                            maxlength="255"
                                            value="{{ old($locale.'.'.$field, $translation?->{$field}) }}"
                                            required
                                        >
                                    @else
                                        <textarea
                                            class="admin-textarea @error($locale.'.'.$field) has-error @enderror"
                                            id="{{ $locale }}-{{ $field }}"
                                            name="{{ $locale }}[{{ $field }}]"
                                            rows="{{ $field === 'about_app' ? 5 : 8 }}"
                                            required
                                        >{{ old($locale.'.'.$field, $translation?->{$field}) }}</textarea>
                                    @endif
                                    @error($locale.'.'.$field)<p class="admin-field__error">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
            </section>

            <div class="admin-settings-actions">
                <button class="admin-button admin-button--primary" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    <span>{{ __('admin.site_settings.save') }}</span>
                </button>
            </div>
        </form>
    </section>
@endsection
