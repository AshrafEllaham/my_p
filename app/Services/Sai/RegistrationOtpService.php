<?php

namespace App\Services\Sai;

use App\Enums\OtpPurposeEnum;
use App\Repositories\Sai\OneTimePasswordRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationOtpService
{
    // Temporary development-only code; replace with random generation and SMS delivery before production.
    private const DEVELOPMENT_CODE = '1234';

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly OneTimePasswordRepository $otps) {}

    /** @param array{phone_code: string, phone: string} $data
     * @return array{verification_expires_at: string}
     */
    public function send(array $data): array
    {
        $expiresAt = now()->addMinutes(5); // OTP expires in 5 minutes
        $identity = $data['phone_code'].$data['phone'];

        DB::transaction(function () use ($identity, $expiresAt): void {

            // Expire any previous unconsumed OTPs for this identity and purpose.
            $this->otps->expireUnconsumed($identity, OtpPurposeEnum::Registration);

            // Create a new OTP record with the development code.
            $this->otps->createRecord([
                'identity' => $identity,
                'purpose' => OtpPurposeEnum::Registration,
                'code_hash' => Hash::make(self::DEVELOPMENT_CODE),
                'expires_at' => $expiresAt,
            ]);
        });

        return ['verification_expires_at' => $expiresAt->format('Y-m-d H:i:s')];
    }

    /** @param array{phone_code: string, phone: string, code: string} $data
     * @return array{verified: true, registration_expires_at: string}
     */
    public function confirm(array $data): array
    {
        $identity = $data['phone_code'].$data['phone'];

        $expiresAt = DB::transaction(function () use ($identity, $data): ?string {

            // Find the latest unconsumed OTP for this identity and purpose, regardless of verification status.
            $otp = $this->otps->findLatestUsable($identity, OtpPurposeEnum::Registration, verified: null);

            // If no OTP is found or the maximum number of attempts has been reached, return null.
            if ($otp === null || $otp->attempts >= self::MAX_ATTEMPTS) {
                return null;
            }

            // If the provided code does not match the stored hash, increment the attempts and return null.
            if (! Hash::check($data['code'], $otp->code_hash)) {
                $this->otps->incrementAttempts($otp);

                return null;
            }

            // If the OTP is not yet verified, mark it as verified.
            if ($otp->verified_at === null) {
                $this->otps->markVerified($otp);
            }

            // Return the expiration time of the OTP in ISO 8601 format.
            return $otp->expires_at->format('Y-m-d H:i:s');
        });

        // If the OTP was not found, expired, or exceeded the maximum attempts, throw a validation exception.
        if ($expiresAt === null) {
            throw ValidationException::withMessages([
                'code' => __('messages.auth.otp_invalid_or_expired'),
            ]);
        }

        //
        return [
            'verified' => true,
            'registration_expires_at' => $expiresAt,
        ];
    }
}
