<?php

namespace App\Services\Sai;

use App\Enums\OtpPurposeEnum;
use App\Models\Sai\User;
use App\Repositories\Sai\OneTimePasswordRepository;
use App\Repositories\Sai\UserRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserPasswordResetService
{
    private const DEVELOPMENT_CODE = '1234';

    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly UserRepository $users,
        private readonly OneTimePasswordRepository $otps,
        private readonly DatabaseManager $database,
    ) {}

    /** @param array{identity: string, phone_code?: string|null} $data
     * @return array{verification_expires_at: string}
     */
    public function send(array $data): array
    {
        [$identity, $user] = $this->resolveIdentity($data);
        $expiresAt = now()->addMinutes(5);

        if ($user !== null) {
            $this->database->transaction(function () use ($identity, $user, $expiresAt): void {
                $this->otps->expireUnconsumed($identity, OtpPurposeEnum::PasswordReset);
                $this->otps->createRecord([
                    'user_id' => $user->id,
                    'identity' => $identity,
                    'purpose' => OtpPurposeEnum::PasswordReset,
                    'code_hash' => Hash::make(self::DEVELOPMENT_CODE),
                    'expires_at' => $expiresAt,
                ]);
            });
        }

        return ['verification_expires_at' => $expiresAt->toIso8601String()];
    }

    /** @param array{identity: string, phone_code?: string|null, code: string} $data
     * @return array{verified: true, expires_at: string}
     */
    public function confirm(array $data): array
    {
        [$identity, $user] = $this->resolveIdentity($data);
        $expiresAt = $this->database->transaction(function () use ($identity, $user, $data): ?string {
            $otp = $this->otps->findLatestUsable($identity, OtpPurposeEnum::PasswordReset, verified: null);
            if ($otp === null || $otp->attempts >= self::MAX_ATTEMPTS || $user === null || (int) $otp->user_id !== (int) $user->id) {
                return null;
            }
            if (! Hash::check($data['code'], $otp->code_hash)) {
                $this->otps->incrementAttempts($otp);

                return null;
            }
            $this->otps->markVerified($otp);

            return $otp->expires_at->toIso8601String();
        });

        if ($expiresAt === null) {
            throw ValidationException::withMessages([
                'code' => __('messages.auth.otp_invalid_or_expired'),
            ]);
        }

        return ['verified' => true, 'expires_at' => $expiresAt];
    }

    /** @param array{identity: string, phone_code?: string|null, code: string, password: string} $data
     * @return array{updated: true}
     */
    public function reset(array $data): array
    {
        [$identity, $user] = $this->resolveIdentity($data);
        if ($user === null) {
            throw ValidationException::withMessages([
                'code' => __('messages.auth.otp_invalid_or_expired'),
            ]);
        }

        $updated = $this->database->transaction(function () use ($identity, $user, $data): bool {
            $otp = $this->otps->findLatestUsable($identity, OtpPurposeEnum::PasswordReset, verified: true);
            if ($otp === null || $otp->attempts >= self::MAX_ATTEMPTS || (int) $otp->user_id !== (int) $user->id || ! Hash::check($data['code'], $otp->code_hash)) {
                if ($otp !== null && $otp->attempts < self::MAX_ATTEMPTS) {
                    $this->otps->incrementAttempts($otp);
                }

                return false;
            }

            $this->users->updatePassword($user, $data['password']);
            $this->otps->consumeForUser($otp, (int) $user->id);

            return true;
        });

        if (! $updated) {
            throw ValidationException::withMessages([
                'code' => __('messages.auth.otp_invalid_or_expired'),
            ]);
        }

        return ['updated' => true];
    }

    /** @param array{identity: string, phone_code?: string|null} $data
     * @return array{0: string, 1: User|null}
     */
    private function resolveIdentity(array $data): array
    {
        $raw = trim($data['identity']);
        if (str_contains($raw, '@')) {
            $identity = mb_strtolower($raw);

            return [$identity, $this->users->findByEmail($identity)];
        }

        $phone = preg_replace('/\\D+/', '', $raw) ?? '';
        $phoneCode = $data['phone_code'] ?? '+20';
        $countryDigits = ltrim($phoneCode, '+');
        if (str_starts_with($phone, $countryDigits) && strlen($phone) > strlen($countryDigits) + 6) {
            $phone = substr($phone, strlen($countryDigits));
        }
        $user = $this->users->findByPhone($phoneCode, $phone);

        return [$phoneCode.$phone, $user];
    }
}
