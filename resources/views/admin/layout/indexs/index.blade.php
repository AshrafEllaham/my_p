<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/brand/saey-mark.svg') }}" type="image/svg+xml">
    <title>@yield('page-title', __('admin.dashboard')) | {{ __('admin.app_name') }}</title>
    @include('admin.layout.inc._css')
    @stack('css')
</head>
<body class="admin-body @hasSection('auth-content') admin-body--auth @endif">
    @hasSection('auth-content')
        <main class="admin-auth-page" id="main-content">
            @yield('auth-content')
        </main>
    @else
        <div class="admin-shell" data-admin-shell>
            @include('admin.layout.inc.sidebar')
            <div class="admin-main">
                @include('admin.layout.inc.header')
                <main class="admin-content" id="main-content">
                    @include('admin.layout.inc.breadcrumb')
                    @yield('content')
                </main>
                @include('admin.layout.inc.footer')
            </div>
        </div>
        @include('admin.layout.inc.global_modals')
    @endif
    @include('admin.layout.inc.loader')
    @include('admin.layout.inc._js')
    @stack('js')
    @yield('js')
</body>
</html>
