<?php

if (! function_exists('showButton')) {
    function showButton(string $route, string $title): string
    {
        return sprintf(
            '<a class="admin-action admin-action--show" href="%s" title="%s" aria-label="%s">%s</a>',
            e($route), e($title), e($title),
            '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>',
        );
    }
}

if (! function_exists('settingsButton')) {
    function settingsButton(string $route, string $title): string
    {
        return sprintf(
            '<a class="admin-action admin-action--settings" href="%s" title="%s" aria-label="%s">%s</a>',
            e($route), e($title), e($title),
            '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-2.92 1.18V20h-2v-.48a1.7 1.7 0 0 0-2.92-1.18l-.06.06L9 16.98l.06-.06A1.7 1.7 0 0 0 7.84 14H7v-2h.84a1.7 1.7 0 0 0 1.22-2.92L9 9.06l1.42-1.42.06.06A1.7 1.7 0 0 0 13.4 6.5V6h2v.5a1.7 1.7 0 0 0 2.92 1.18l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 20.96 12H21v2h-.04A1.7 1.7 0 0 0 19.4 15Z"/></svg>',
        );
    }
}

if (! function_exists('banButton')) {
    function banButton(string $type, int|string $id, bool $isBanned): string
    {
        $action = $isBanned ? __('admin.actions.unban') : __('admin.actions.ban');

        return sprintf(
            '<button class="admin-action admin-action--danger js-ban-action" type="button" data-type="%s" data-id="%s" data-banned="%s" title="%s" aria-label="%s">%s</button>',
            e($type), e((string) $id), $isBanned ? '1' : '0', e($action), e($action),
            '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m6 6 12 12"/></svg>',
        );
    }
}

if (! function_exists('helperTrans')) {
    /** @param array<string, mixed> $replace */
    function helperTrans(string $key, array $replace = []): string
    {
        $translationKey = str_starts_with($key, 'admin.') ? $key : "admin.{$key}";

        return __($translationKey, $replace);
    }
}
