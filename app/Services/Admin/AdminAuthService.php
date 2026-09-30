<?php

namespace App\Services\Admin;

use App\Models\Admin\Admin;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AdminAuthService
{
    public function __construct(
        private readonly AuthFactory $auth,
    ) {}

    /** @param array{email: string, password: string} $credentials */
    public function login(array $credentials, bool $remember = false): bool
    {
        $guard = $this->auth->guard('admin');
        $authenticated = $guard->attempt([
            ...$credentials,
            'is_active' => true,
            'is_blocked' => false,
        ], $remember);

        if (! $authenticated) {
            return false;
        }

        $admin = $guard->user();

        if ($admin instanceof Admin) {
            $admin->update(['last_login_at' => now()]);
        }

        return true;
    }

    public function logout(): void
    {
        $this->auth->guard('admin')->logout();
    }
}
