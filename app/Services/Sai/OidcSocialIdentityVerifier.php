<?php

namespace App\Services\Sai;

use App\Contracts\Sai\SocialIdentityVerifier;
use App\Enums\SocialLoginProviderEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class OidcSocialIdentityVerifier implements SocialIdentityVerifier
{
    private const GOOGLE_JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const APPLE_JWKS_URL = 'https://appleid.apple.com/auth/keys';

    public function verify(SocialLoginProviderEnum $provider, string $idToken): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            $this->invalidToken();
        }

        [$encodedHeader, $encodedClaims, $encodedSignature] = $parts;
        $header = $this->decodeJson($encodedHeader);
        $claims = $this->decodeJson($encodedClaims);
        $signature = $this->decodeBase64Url($encodedSignature);

        if (($header['alg'] ?? null) !== 'RS256' || ! is_string($header['kid'] ?? null) || $signature === null) {
            $this->invalidToken();
        }

        $key = collect($this->keys($provider))->firstWhere('kid', $header['kid']);
        if (! is_array($key) || ($key['kty'] ?? null) !== 'RSA') {
            $this->invalidToken();
        }

        $publicKey = openssl_pkey_get_public($this->rsaPublicKey($key));
        if ($publicKey === false || openssl_verify(
            $encodedHeader.'.'.$encodedClaims,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256,
        ) !== 1) {
            $this->invalidToken();
        }

        $claims = $this->validateClaims($provider, $claims);

        return [
            'sub' => $claims['sub'],
            'email' => mb_strtolower($claims['email']),
            'email_verified' => true,
            'name' => is_string($claims['name'] ?? null) ? $claims['name'] : null,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function keys(SocialLoginProviderEnum $provider): array
    {
        $url = match ($provider) {
            SocialLoginProviderEnum::Google => self::GOOGLE_JWKS_URL,
            SocialLoginProviderEnum::Apple => self::APPLE_JWKS_URL,
        };

        return Cache::remember('oidc-jwks:'.$provider->value, now()->addHour(), function () use ($url): array {
            return Http::timeout(5)->get($url)->throw()->json('keys', []);
        });
    }

    /** @param array<string, mixed> $claims @return array{sub: string, email: string, email_verified: bool, name?: string} */
    private function validateClaims(SocialLoginProviderEnum $provider, array $claims): array
    {
        $config = config('services.social.'.$provider->value, []);
        $audiences = $config['client_ids'] ?? [];
        $audiences = is_array($audiences) ? array_filter($audiences) : [];
        $issuerIsValid = match ($provider) {
            SocialLoginProviderEnum::Google => in_array($claims['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true),
            SocialLoginProviderEnum::Apple => ($claims['iss'] ?? null) === 'https://appleid.apple.com',
        };
        $tokenAudiences = $claims['aud'] ?? [];
        $tokenAudiences = is_array($tokenAudiences) ? $tokenAudiences : [$tokenAudiences];
        $audienceIsValid = count(array_intersect($audiences, $tokenAudiences)) > 0;
        $verifiedEmail = in_array($claims['email_verified'] ?? null, [true, 'true', 1, '1'], true);

        if ($audiences === [] || ! $issuerIsValid || ! $audienceIsValid
            || ! is_numeric($claims['exp'] ?? null) || (int) $claims['exp'] <= time()
            || (isset($claims['nbf']) && (! is_numeric($claims['nbf']) || (int) $claims['nbf'] > time()))
            || ! is_string($claims['sub'] ?? null) || $claims['sub'] === ''
            || ! is_string($claims['email'] ?? null) || ! $verifiedEmail) {
            $this->invalidToken();
        }

        return $claims;
    }

    /** @param array<string, mixed> $key */
    private function rsaPublicKey(array $key): string
    {
        $modulus = $this->decodeBase64Url($key['n'] ?? '');
        $exponent = $this->decodeBase64Url($key['e'] ?? '');
        if ($modulus === null || $exponent === null) {
            $this->invalidToken();
        }

        $rsaKey = $this->derSequence($this->derInteger($modulus).$this->derInteger($exponent));
        $algorithm = hex2bin('300d06092a864886f70d0101010500');
        $subjectPublicKeyInfo = $this->derSequence($algorithm.$this->der(0x03, "\0".$rsaKey));

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")."-----END PUBLIC KEY-----\n";
    }

    private function derInteger(string $value): string
    {
        return $this->der(0x02, (ord($value[0]) > 0x7F ? "\0" : '').$value);
    }

    private function derSequence(string $value): string
    {
        return $this->der(0x30, $value);
    }

    private function der(int $tag, string $value): string
    {
        $length = strlen($value);
        if ($length < 128) {
            return chr($tag).chr($length).$value;
        }

        $lengthBytes = ltrim(pack('N', $length), "\0");

        return chr($tag).chr(0x80 | strlen($lengthBytes)).$lengthBytes.$value;
    }

    /** @return array<string, mixed> */
    private function decodeJson(string $segment): array
    {
        $decoded = $this->decodeBase64Url($segment);
        $value = $decoded === null ? null : json_decode($decoded, true);
        if (! is_array($value)) {
            $this->invalidToken();
        }

        return $value;
    }

    private function decodeBase64Url(string $value): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return null;
        }

        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return $decoded === false ? null : $decoded;
    }

    private function invalidToken(): never
    {
        throw ValidationException::withMessages([
            'id_token' => __('messages.auth.social_token_invalid'),
        ]);
    }
}
