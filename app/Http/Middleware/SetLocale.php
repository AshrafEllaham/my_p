<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales', []));
        $locale = $request->hasSession()
            ? $request->session()->get('locale')
            : null;

        if (! is_string($locale) || $locale === '') {
            $locale = $request->getPreferredLanguage($supportedLocales)
                ?? config('app.locale');
        }

        if (! is_string($locale) || ! in_array($locale, $supportedLocales, true)) {
            $locale = (string) config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
