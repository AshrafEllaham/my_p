<?php

namespace App\Services\Sai;

use App\Enums\OtpPurposeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\User;
use App\Repositories\Sai\OneTimePasswordRepository;
use App\Repositories\Sai\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly OneTimePasswordRepository $otps,
    ) {}

    /** @param array{phone_code: string, phone: string, email: string, password: string, password_confirmation: string} $data */
    public function register(array $data, string $locale): User
    {
        return DB::transaction(function () use ($data, $locale): User {
            $identity = $data['phone_code'].$data['phone'];
            $this->otps->expireUnconsumed($identity, OtpPurposeEnum::Registration);

            $user = $this->users->createRecord([
                'email' => $data['email'],
                'phone_code' => $data['phone_code'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'status' => UserStatusEnum::PendingVerification,
                'preferred_locale' => $locale,
            ]);

            $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);

            $this->otps->createRecord([
                'user_id' => $user->id,
                'identity' => $identity,
                'purpose' => OtpPurposeEnum::Registration,
                'code_hash' => Hash::make($otp),
                'expires_at' => $expiresAt,
            ]);

            $user->setAttribute('verification_expires_at', $expiresAt->toISOString());

            return $user;
        });
    }
}
