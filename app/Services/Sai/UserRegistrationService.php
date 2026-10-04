<?php

namespace App\Services\Sai;

use App\Enums\OtpPurposeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\User;
use App\Repositories\Sai\OneTimePasswordRepository;
use App\Repositories\Sai\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $otp = $this->otps->findLatestUsable($identity, OtpPurposeEnum::Registration, verified: true);

            if ($otp === null) {
                throw ValidationException::withMessages([
                    'phone' => __('messages.auth.registration_otp_required'),
                ]);
            }

            $user = $this->users->createRecord([
                'email' => $data['email'],
                'phone_code' => $data['phone_code'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'status' => UserStatusEnum::Active,
                'preferred_locale' => $locale,
            ]);

            $this->otps->consumeForUser($otp, $user->id);

            return $user;
        });
    }
}
