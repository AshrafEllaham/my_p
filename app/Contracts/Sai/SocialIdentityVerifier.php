<?php

namespace App\Contracts\Sai;

use App\Enums\SocialLoginProviderEnum;

interface SocialIdentityVerifier
{
    /** @return array{sub: string, email: string, email_verified: bool, name?: string} */
    public function verify(SocialLoginProviderEnum $provider, string $idToken): array;
}
