<?php

namespace App\Services\Sai;

use Tymon\JWTAuth\JWTAuth;

class AccountSessionService
{
    public function __construct(private readonly JWTAuth $jwt) {}

    public function logout(string $token): void
    {
        $this->jwt->setToken($token)->invalidate();
    }
}
