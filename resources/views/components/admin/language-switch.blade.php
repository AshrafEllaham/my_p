@php
    $currentLocale = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale();
    $nextLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $nextLanguage = config("laravellocalization.supportedLocales.{$nextLocale}.native", strtoupper($nextLocale));
    $localizedUrl = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(
        $nextLocale,
        null,
        [],
        true,
    );
@endphp

<a
    href="{{ $localizedUrl }}"
    class="admin-language-switch {{ $attributes->get('class') }}"
    hreflang="{{ $nextLocale }}"
    lang="{{ $nextLocale }}"
    rel="alternate"
    title="{{ __('admin.actions.switch_language') }}"
    {{ $attributes->except('class') }}
>
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9S14.4 18.5 12 21c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3Z"/></svg>
    <span>{{ $nextLanguage }}</span>
</a>
